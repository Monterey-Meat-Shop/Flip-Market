<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Payment;
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
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;

class TransactionResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $pluralModelLabel = 'Transactions';
    protected static ?string $modelLabel = 'Transaction';
    protected static ?string $slug = 'transactions';

    // This method filters records based on user roles.
    public static function getEloquentQuery(): Builder
    {
        // Removed the withoutGlobalScopes() call to fix the soft delete issue.
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
                // Hidden fields to store calculated amounts
                Hidden::make('total_amount')
                    ->dehydrateStateUsing(fn (Get $get) => collect($get('orderItems') ?? [])->sum('sub_total'))
                    ->default(0.00),
                
                Hidden::make('final_amount')
                    ->dehydrateStateUsing(fn (Get $get) => collect($get('orderItems') ?? [])->sum('sub_total'))
                    ->default(0.00),

                // Section for general order information
                Section::make('Order Information')
                    ->schema([
                        // Select field for customer
                        Select::make('customerID')
                            ->label('Customer Name')
                            ->relationship(name: 'customer', titleAttribute: 'first_name')
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
                                        modifyQueryUsing: fn (Builder $query) => $query
                                            ->whereNotIn('status', ['pre_order', 'out_of_stock']),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->price);
                                            $set('sub_total', $product->price * $get('quantity'));
                                        } else {
                                            $set('unit_price', 0);
                                            $set('sub_total', 0);
                                        }
                                    })
                                    ->columnSpan(4),

                                // New Select field for shoe size, dynamically populated
                                Select::make('size')
                                    ->label('Shoe Size')
                                    ->options(function (Get $get): array {
                                        // Get the selected product ID from the form state
                                        $productID = $get('productID');
                                        
                                        // If a product is selected, find the product and get its sizes
                                        if ($productID) {
                                            $product = Product::find($productID);
                                            // The `size` column is an array, so we can use it directly
                                            if ($product && is_array($product->size)) {
                                                // We map the array to a key-value pair for the Select component
                                                return array_combine($product->size, $product->size);
                                            }
                                        }
                                        
                                        // Return an empty array if no product is selected or no sizes are available
                                        return [];
                                    })
                                    ->required()
                                    ->live()
                                    ->columnSpan(2),
                                    
                                // Text input for quantity
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $unitPrice = $get('unit_price');
                                        if ($unitPrice && $state) {
                                            $set('sub_total', $unitPrice * $state);
                                        }
                                    })
                                    ->rules([
                                        // Custom validation to check stock quantity
                                        function (Get $get) {
                                            return function (string $attribute, $value, Closure $fail) use ($get) {
                                                $product = Product::find($get('productID'));
                                                if ($product && $value > $product->stock_quantity) {
                                                    $fail("The quantity for '{$product->name}' cannot exceed the available stock of {$product->stock_quantity}.");
                                                }
                                            };
                                        },
                                    ])
                                    ->columnSpan(2),

                                // Text input for unit price
                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan(2),

                                // Text input for subtotal
                                TextInput::make('sub_total')
                                    ->numeric()
                                    ->required()
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
                
                // Section for order status
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

    // This method defines the table's columns, filters, and actions.
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
                
                // Directly accesses the 'payment_method' column which is a string.
                TextColumn::make('payment.paymentMethod.method_name')
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
                TextColumn::make('payment.reference_number')
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
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // This method defines the relationships that will be displayed on the table.
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

    // This method restricts who can view the resource page.
    public static function canViewAny(): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'cashier']);
    }
}
