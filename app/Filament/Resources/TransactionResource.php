<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $pluralModelLabel = 'Transactions';
    protected static ?string $modelLabel = 'Transaction';
    protected static ?string $slug = 'transactions';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // If the authenticated user has the 'admin' role, return all records.
        if (auth()->user()->hasRole('admin')) {
            return $query;
        }

        // Otherwise, filter the query to only show orders for the authenticated customer.
        return $query->where('customerID', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Hidden field to store the calculated total amount.
                Hidden::make('total_amount')
                    ->dehydrateStateUsing(fn (Get $get) => collect($get('orderItems') ?? [])->sum('sub_total'))
                    ->default(0.00),

                // This is the new hidden field to ensure final_amount is sent to the database.
                Hidden::make('final_amount')
                    ->dehydrateStateUsing(fn (Get $get) => collect($get('orderItems') ?? [])->sum('sub_total'))
                    ->default(0.00),

                // Section for general order information
                Section::make('Order Information')
                    ->schema([
                        // Select field for customer
                        Select::make('customerID')
                            ->label('Customer Name')
                            ->relationship(name: 'customer', titleAttribute: 'first_name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('first_name'))
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->required(),

                        // Date picker for the order date
                        DateTimePicker::make('order_date')
                            ->default(now())
                            ->required()
                            ->disabled()
                            ->dehydrated(true),
                    ])->columns(2),

                // Section for order items using a repeater
                Section::make('Order Items')
                    ->schema([
                        Repeater::make('orderItems')
                            ->label('Products List')
                            ->relationship('orderItems')
                            ->schema([
                                // Select field for product
                                Select::make('productID')
                                    ->relationship(
                                        name: 'product',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn (Builder $query) => $query->whereNotIn('status', ['pre_order', 'out_of_stock']),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        // Fetch the product price and update fields
                                        $product = Product::find($get('productID'));
                                        if ($product) {
                                            $set('unit_price', $product->price);
                                            // Recalculate sub_total based on the new unit price
                                            $set('sub_total', $product->price * $get('quantity'));
                                        } else {
                                            // Reset fields if product not found
                                            $set('unit_price', 0);
                                            $set('sub_total', 0);
                                        }

                                        // Reset size and colorway
                                        $set('product_variant_id', null);
                                        $set('size', null);
                                        $set('colorway', null);
                                    })
                                    ->columnSpan(4),

                                // Select field for shoe size, dynamically populated from the product variants
                                Select::make('product_variant_id')
                                    ->label('Shoe Size')
                                    ->options(function (Get $get): array {
                                        $productID = $get('productID');
                                        if ($productID) {
                                            $variants = ProductVariant::where('product_id', $productID)->get();
                                            // The key is the variant ID and the value is the size
                                            return $variants->pluck('size', 'id')->toArray();
                                        }
                                        return [];
                                    })
                                    ->live()
                                    ->required()
                                    ->visible(fn (Get $get) => filled($get('productID')))
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        // Get the selected product variant and set the size and colorway
                                        $productVariant = ProductVariant::find($get('product_variant_id'));
                                        if ($productVariant) {
                                            $set('size', $productVariant->size);
                                            $set('colorway', $productVariant->colorway);
                                        } else {
                                            $set('size', null);
                                            $set('colorway', null);
                                        }
                                    })
                                    ->columnSpan(2),
                                
                                TextInput::make('colorway')
                                    ->label('Colorway')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan(2),

                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $unitPrice = $get('unit_price');
                                        if ($unitPrice && $state) {
                                            $set('sub_total', $unitPrice * $state);
                                        } else {
                                            $set('sub_total', 0);
                                        }
                                    })
                                    ->rules([
                                        function (Get $get) {
                                            return function (string $attribute, $value, Closure $fail) use ($get) {
                                                // Find the specific product variant based on the variant ID
                                                $productVariant = ProductVariant::find($get('product_variant_id'));

                                                if ($productVariant && $value > $productVariant->stock_quantity) {
                                                    $fail("The quantity for this product variant cannot exceed the available stock of {$productVariant->stock_quantity}.");
                                                }
                                            };
                                        },
                                    ])
                                    ->columnSpan(2),

                                // Text input for unit price, disabled since it's set dynamically
                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan(2),

                                // Text input for subtotal, disabled since it's set dynamically
                                TextInput::make('sub_total')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan(2),
                                
                                Hidden::make('size')
                                    ->label('Size')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan(2),

                            ])->columns(12)
                            ->collapsible()
                            ->defaultItems(1)
                            ->live(),
                    ]),

                Section::make('Payment Information')
                    ->schema([
                        // Select field for payment method. It now saves the method name instead of the ID.
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options(
                                PaymentMethod::query()
                                    ->whereIn('method_name', ['Cash', 'Gcash'])
                                    ->pluck('method_name', 'method_name')
                            )
                            ->required()
                            ->live(),

                        // Text input for reference number, conditionally visible for Gcash.
                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->placeholder('Reference no.')
                            ->visible(fn (Get $get) => $get('payment_method') === 'Gcash')
                            ->required(fn (Get $get) => $get('payment_method') === 'Gcash'),

                        // Select field for payment status
                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'paid' => 'Paid',
                                'unpaid' => 'Unpaid',
                                'verified' => 'Verified',
                            ])
                            ->default('paid')
                            ->required(),
                    ])->columns(3),

                Section::make('Order Status')
                    ->schema([
                        // Placeholder for displaying the total order amount
                        Placeholder::make('total_amount_placeholder')
                            ->label('Total Order Amount')
                            ->content(function (Get $get) {
                                $subTotals = collect($get('orderItems'))
                                    ->pluck('sub_total')
                                    ->filter();
                                return number_format($subTotals->sum(), 2);
                            })
                            ->live(),

                        // Select field for order status
                        Select::make('order_status')
                            ->label('Order Status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                                'completed' => 'Completed',
                            ])
                            ->default('completed')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    /**
     * This method defines the table's columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            // Corrected default sort to use the 'orderID' column.
            ->defaultSort('orderID', 'desc')
            ->columns([
                TextColumn::make('customer.first_name')
                    ->label('Customer Name')
                    ->searchable(query: fn (Builder $query, string $search): Builder =>
                        $query->whereHas('customer', fn (Builder $q) =>
                            $q->where('first_name', 'like', "%{$search}%"))
                    ),

                TextColumn::make('orderItems.product.name')
                    ->label('Products')
                    ->listWithLineBreaks(),

                // Displaying the payment method directly from the order
                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? 'N/A'),

                // Displaying the total amount of the order
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->numeric()
                    ->sortable()
                    ->money('PHP'),

                // Displaying the order status instead of transaction status
                TextColumn::make('order_status')
                    ->label('Order Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending', 'processing' => 'warning',
                        'shipped', 'delivered', 'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime()
                    ->sortable(),

                // Displaying the reference number from the related payment record
                TextColumn::make('reference_number')
                    ->label('Reference No.')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? 'N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Archived Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('payment_status')
                    ->options([
                        'paid' => 'Paid',
                        'unpaid' => 'Unpaid',
                        'verified' => 'Verified',
                    ])
                    ->label('Payment Status'),
                SelectFilter::make('order_status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ])
                    ->label('Order Status'),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('order_from')
                            ->label('From Date'),
                        DatePicker::make('order_until')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['order_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['order_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['order_from'] ?? null) {
                            $indicators[] = Indicator::make('Order from ' . now()->parse($data['order_from'])->toFormattedDateString())
                                ->remove('order_from');
                        }

                        if ($data['order_until'] ?? null) {
                            $indicators[] = Indicator::make('Order until ' . now()->parse($data['order_until'])->toFormattedDateString())
                                ->remove('order_until');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * This method defines the relationships that will be displayed on the table.
     */
    public static function getRelations(): array
    {
        return [
            // You can add more relations here in the future if needed.
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::query()
            ->where('order_status', 'completed')
            ->whereDate('created_at', today())
            ->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];  
    }

    /**
     * This method restricts who can view the resource page.
     */
    public static function canViewAny(): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'cashier']);
    }

    /**
     * This function now explicitly handles stock deduction and
     * sanitizes the form data before saving the transaction.
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $totalAmount = collect($data['orderItems'] ?? [])
            ->pluck('sub_total')
            ->filter()
            ->sum();

        $data['total_amount'] = $totalAmount;
        $data['final_amount'] = $totalAmount;

        return $data;
    }

    // Deduct stock after the order is actually created
    public static function afterCreate(Model $record): void
    {
        if ($record instanceof Order && $record->payment_status === 'paid') {
            // refresh to make sure orderItems are loaded
            $record->load('orderItems.productVariant');

            try {
                $record->deductStock();
            } catch (\Exception $e) {
                Log::error("Stock deduction failed: " . $e->getMessage());
                throw $e; // rethrow so cashier sees the error
            }
        }
    }
}
