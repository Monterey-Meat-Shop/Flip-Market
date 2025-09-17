<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\User;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Discount;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;

class TransactionResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $pluralModelLabel = 'Transactions';
    protected static ?string $modelLabel = 'Transaction';
    protected static ?string $slug = 'transactions';

    public static function getEloquentQuery(): Builder
    {

        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // If no user is authenticated, return empty query
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Admin: View all transactions
        if ($user->hasRole(['admin', 'cashier'])) {
            return $query;
        }
    
        // Cashier: Only view 'guest' transactions using a direct, reliable filter.
        // This ensures a cashier cannot access other users' data.
        if ($user->hasRole('cashier')) {
            $guestUser = User::where('first_name', 'guest')->first();
            if ($guestUser) {
                return $query->where('customerID', $guestUser->id);
            }
            // If the guest user does not exist, show an empty table to prevent errors.
            return $query->whereRaw('1 = 0'); // Better than where('id', null)
        }
    
        // Standard user: Only view their own transactions.
        return $query->where('customerID', $user->id);
    }

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();
    
        // Only show 'Sales' group for admin users
        if ($user && $user->hasRole('admin')) {
           return 'Sales';
        }
    
        // Return null to hide from Sales group for non-admin users
        return null;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole(['admin', 'cashier']);
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        return auth()->user()->hasRole(['admin', 'cashier']); //need to changes
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('cashier');
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('admin');
    }

    public static function canForceDelete(Model $record): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('admin');
    }

    public static function canRestore(Model $record): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Hidden field to store the calculated total amount
                Hidden::make('total_amount')
                    ->dehydrateStateUsing(fn (Get $get) => collect($get('orderItems') ?? [])->sum('sub_total'))
                    ->default(0.00),

                Hidden::make('final_amount')
                    ->dehydrateStateUsing(fn(Get $get) => collect($get('orderItems') ?? [])->sum('sub_total'))
                    ->default(0.00),

                Hidden::make('total_amount')
                    ->dehydrateStateUsing(fn (Get $get) => collect($get('orderItems') ?? [])->sum('sub_total'))
                    ->default(0.00),

                Hidden::make('final_amount')
                    ->dehydrateStateUsing(function (Get $get) {
                        $subTotal = collect($get('orderItems') ?? [])->sum('sub_total');
                        $discountID = $get('discountID');
        
                        if ($discountID) {
                            $discount = Discount::find($discountID);
                            if ($discount) {
                                return $discount->getFinalPrice($subTotal);
                            }
                        }
        
                        return $subTotal;
                    })
                    ->default(0.00),

                // Section for general order information
                Section::make('Order Information')
                    ->schema([
                        Select::make('customerID')
                            ->label('Customer Name')
                            ->relationship(
                                name: 'customer',
                                titleAttribute: 'first_name',
                                modifyQueryUsing: fn (Builder $query) => $query->where('first_name', 'guest')->orderBy('first_name')
                            )
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->required(),

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
                                Select::make('productID')
                                    ->relationship(
                                    'product',
                                    'name',
                                    modifyQueryUsing: fn (Builder $query) => $query->whereNotIn('status', ['pre_order', 'out_of_stock']),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $product = Product::find($get('productID'));
                                        if ($product) {
                                            $originalPrice = $product->price;
                                            $finalPrice = $originalPrice;
                                            $discountName = null;
                                            $discountAmount = 0;
        
                                            $discountID = $get('../../discountID');
                                            Log::info("Debug - Processing product:", [
                                                'productID' => $product->productID,
                                                'original_price' => $originalPrice,
                                                'discountID' => $discountID
                                            ]);
        
                                            if ($discountID) {
                                                $discount = Discount::find($discountID);
                                                Log::info("Debug - Discount found:", [
                                                    'discount' => $discount ? $discount->toArray() : 'null',
                                                    'product_has_discount' => $discount ? $product->discounts->contains($discount) : false
                                                ]);
            
                                                if ($discount && $product->discounts->contains($discount)) {
                                                    $finalPrice = $discount->getFinalPrice($originalPrice);
                                                    $discountName = $discount->name;
                                                    $discountAmount = $originalPrice - $finalPrice;
                
                                                    Log::info("Debug - Discount calculation details:", [
                                                        'original_price' => $originalPrice,
                                                        'original_price_type' => gettype($originalPrice),
                                                        'final_price' => $finalPrice,
                                                        'final_price_type' => gettype($finalPrice),
                                                        'calculated_discount_amount' => $discountAmount,
                                                        'discount_amount_type' => gettype($discountAmount),
                                                        'discount_name' => $discountName,
                                                        'discount_type' => $discount->discount_type,
                                                        'discount_value' => $discount->discount_value
                                                    ]);
                                                }
                                            }
        
                                            // Set the values
                                            $set('original_price', $originalPrice);
                                            $set('discount_name', $discountName);
                                            $set('discount_amount', $discountAmount);
                                            $set('unit_price', $finalPrice);
                                            $set('sub_total', $finalPrice * ($get('quantity') ?? 1));
        
                                            Log::info("Debug - Values being set:", [
                                                'setting_original_price' => $originalPrice,
                                                'setting_discount_name' => $discountName,
                                                'setting_discount_amount' => $discountAmount,
                                                'setting_unit_price' => $finalPrice
                                            ]);
                                        }
    
                                        // Reset other fields
                                        $set('product_variant_id', null);
                                        $set('size', null);
                                        $set('colorway', null);
                                    })
                                    ->columnSpan(4),

                                Select::make('product_variant_id')
                                    ->label('Shoe Size')
                                    ->options(function (Get $get): array {
                                        $productID = $get('productID');
                                        if ($productID) {
                                            return ProductVariant::where('product_id', $productID)
                                                ->pluck('size', 'id')
                                                ->filter()
                                                ->toArray();
                                        }
                                        return [];
                                    })
                                    ->live()
                                    ->required()
                                    ->visible(fn (Get $get) => filled($get('productID')))
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $variant = ProductVariant::find($get('product_variant_id'));
                                        if ($variant) {
                                            $set('size', $variant->size);
                                            $set('colorway', $variant->colorway);
                                        } else {
                                            $set('size', null);
                                            $set('colorway', null);
                                        }
                                    })
                                    ->columnSpan(2),

                                Hidden::make('original_price')
                                    ->dehydrated(true),

                                Hidden::make('discount_name')
                                    ->dehydrated(true),

                                Hidden::make('discount_amount')
                                    ->dehydrated(true),

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
                                        $originalPrice = $get('original_price');
                                        $discountAmount = $get('discount_amount');
        
                                        // Update sub_total
                                        $set('sub_total', ($unitPrice && $state) ? $unitPrice * $state : 0);
        
                                        // If there's no original_price set yet, get it from the product
                                        if (!$originalPrice && $get('productID')) {
                                            $product = Product::find($get('productID'));
                                            if ($product) {
                                                $set('original_price', $product->price);
                                            }
                                        }
                                    })
                                    ->columnSpan(2),

                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan(2),

                                TextInput::make('sub_total')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan(2),

                                Hidden::make('size')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan(2),
                            ])->columns(12)
                            ->collapsible()
                            ->defaultItems(1)
                            ->live(),
                    ]),

                Section::make('Discount Information')
                            ->schema([
                                Select::make('discountID')
                                    ->label('Apply Discount')
                                    ->options(function (Get $get): array {
                                        $orderItems = $get('orderItems') ?? [];
                                        $productIds = collect($orderItems)->pluck('productID')->filter()->toArray();
                
                                        if (empty($productIds)) {
                                            return [];
                                        }
                
                                        return Discount::where('is_active', true)
                                            ->where(function($query) {
                                                $query->where('start_date', '<=', now())
                                                      ->orWhereNull('start_date');
                                            })
                                            ->where(function($query) {
                                                $query->where('end_date', '>=', now())
                                                      ->orWhereNull('end_date');
                                            })
                                            ->whereHas('products', function($query) use ($productIds) {
                                                $query->whereIn('product_id', $productIds);
                                            })
                                            ->pluck('name', 'discountID')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        Log::info("Transaction discount selection changed", ['discountID' => $state]);
                
                                        // Trigger recalculation of totals when discount changes
                                        $orderItems = $get('orderItems') ?? [];
                                        foreach ($orderItems as $index => $item) {
                                            if (isset($item['productID'])) {
                                                $product = Product::find($item['productID']);
                                                if ($product) {
                                                    $originalPrice = $product->price;
                                                    $finalPrice = $originalPrice;
                                                    $discountName = null;
                                                    $discountAmount = 0;
                            
                                                    if ($state) {
                                                        $discount = Discount::find($state);
                                                        Log::info("Processing discount for transaction item {$index}", [
                                                            'productID' => $item['productID'],
                                                            'discount_found' => $discount ? true : false,
                                                            'product_has_discount' => $discount ? $product->discounts->contains($discount) : false
                                                        ]);
                                
                                                        if ($discount && $product->discounts->contains($discount)) {
                                                            $finalPrice = $discount->getFinalPrice($originalPrice);
                                                            $discountName = $discount->name;
                                                            $discountAmount = $originalPrice - $finalPrice;
                                    
                                                            Log::info("Applying discount to transaction item {$index}", [
                                                                'original_price' => $originalPrice,
                                                                'final_price' => $finalPrice,
                                                                'discount_amount' => $discountAmount,
                                                                'discount_name' => $discountName
                                                            ]);
                                                        }
                                                    }
                            
                                                    $quantity = $item['quantity'] ?? 1;
                            
                                                    // Update ALL fields including discount information
                                                    $set("orderItems.{$index}.original_price", $originalPrice);
                                                    $set("orderItems.{$index}.discount_name", $discountName);
                                                    $set("orderItems.{$index}.discount_amount", $discountAmount);
                                                    $set("orderItems.{$index}.unit_price", $finalPrice);
                                                    $set("orderItems.{$index}.sub_total", $finalPrice * $quantity);
                                                }
                                            }
                                        }
                                    }),
                            ])->columns(1),

                Section::make('Payment Information')
                    ->relationship('payment')
                    ->schema([
                        Select::make('payment_methodID')
                            ->label('Payment Method')
                            ->relationship(
                                name: 'paymentMethod', 
                                titleAttribute: 'method_name',
                                modifyQueryUsing: fn (Builder $query) => $query->whereIn('method_name', ['Cash', 'GCash'])
                            )
                            ->required()
                            ->preload()
                            ->dehydrated(true)
                            ->live(),

                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->placeholder('Reference no.')
                            ->visible(function (Get $get) {
                                $paymentMethodId = $get('payment_methodID');
                                if (!$paymentMethodId) {
                                    return false;
                                }
                
                                $paymentMethod = PaymentMethod::find($paymentMethodId);
                                return $paymentMethod && strtolower($paymentMethod->method_name) === 'gcash';
                            })
                            ->required(function (Get $get) {
                                $paymentMethodId = $get('payment_methodID');
                                if (!$paymentMethodId) {
                                    return false;
                                }
                
                                $paymentMethod = PaymentMethod::find($paymentMethodId);
                                return $paymentMethod && strtolower($paymentMethod->method_name) === 'gcash';
                            })
                            ->dehydrated(true),

                        TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->dehydrated(true),

                        Select::make('status')
                            ->label('Payment Status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'paid' => 'Paid',
                                'verified' => 'Verified',
                            ])
                            ->default('paid') // Changed default to 'paid' for transactions
                            ->required()
                            ->dehydrated(true),
                    ])
                    ->columns(3),

                        Section::make('Order Summary')
                            ->schema([
                                Placeholder::make('subtotal_placeholder')
                                    ->label('Subtotal (Before Discount)')
                                    ->content(function (Get $get) {
                                        $orderItems = $get('orderItems') ?? [];
                                        $subtotal = 0;
                
                                        foreach ($orderItems as $item) {
                                            if (isset($item['productID']) && isset($item['quantity'])) {
                                                $product = Product::find($item['productID']);
                                                if ($product) {
                                                    $subtotal += $product->price * $item['quantity'];
                                                }
                                            }
                                        }
                
                                        return '₱' . number_format($subtotal, 2);
                                    })
                                    ->live(),
            
                                Placeholder::make('discount_amount_placeholder')
                                    ->label('Discount Amount')
                                    ->content(function (Get $get) {
                                        $orderItems = $get('orderItems') ?? [];
                                        $discountID = $get('discountID');
                
                                        if (!$discountID) {
                                            return '₱0.00';
                                        }
                
                                        $subtotal = 0;
                                        foreach ($orderItems as $item) {
                                            if (isset($item['productID']) && isset($item['quantity'])) {
                                                $product = Product::find($item['productID']);
                                                if ($product) {
                                                    $subtotal += $product->price * $item['quantity'];
                                                }
                                            }
                                        }
                
                                        $discount = Discount::find($discountID);
                                        if ($discount) {
                                            $finalPrice = $discount->getFinalPrice($subtotal);
                                            $discountAmount = $subtotal - $finalPrice;
                                            return '-₱' . number_format($discountAmount, 2);
                                        }
                
                                        return '₱0.00';
                                    })
                                    ->live(),

                                Placeholder::make('total_amount_placeholder')
                                    ->label('Final Total Amount')
                                    ->content(function (Get $get) {
                                        $subTotals = collect($get('orderItems'))->pluck('sub_total')->sum();
                                        return '₱' . number_format($subTotals, 2);
                                    })
                                    ->live(),

                                Select::make('order_status')
                                    ->label('Order Status')
                                    ->options([
                                        'processing' => 'Processing',
                                        'completed' => 'Completed',
                                    ])
                                    ->default('completed')
                                    ->required(),
                            ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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

                TextColumn::make('payment.paymentMethod.method_name')
                    ->label('Payment Method')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? 'N/A'),

                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->numeric()
                    ->sortable()
                    ->money('PHP'),

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

    public static function getRelations(): array
    {
        return [
            //
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

    public static function canViewAny(): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'cashier']);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $totalAmount = collect($data['orderItems'] ?? [])
            ->pluck('sub_total')
            ->filter()
            ->sum();

        $data['total_amount'] = $totalAmount;
        $data['final_amount'] = $totalAmount;

        // FIX: Ensure payment status is included
        if (isset($data['payment'])) {
            $data['payment']['status'] = $data['payment']['status'] ?? 'unpaid';
        }

        return $data;
    }
}
