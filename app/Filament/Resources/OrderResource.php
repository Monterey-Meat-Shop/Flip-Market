<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\customer;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\Discount;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Closure;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?string $slug = 'orders';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['payment', 'customer', 'orderItems'])->withTrashed();

        if (auth()->user()->hasRole(['admin', 'manager'])) {
            return $query;
        }

        return $query->where('customerID', auth()->id());
    }

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();

        if ($user && $user->hasRole(['admin'])) {
           return 'Sales';
        }
        return null;
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['admin', 'manager']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole('manager');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole('admin');
    }    

    public static function canForceDelete(Model $record): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function canRestore(Model $record): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function getRouteKeyName(): ?string
    {
        return 'ProductID';
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
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

                Section::make('Customer Information')
                    ->schema([
                        Select::make('customerID')
                            ->label('Customer Name')
                            ->options(
                                Customer::orderBy('first_name')
                                    ->get()
                                    ->mapWithKeys(fn($customer) => [
                                        $customer->customerID => trim($customer->first_name . ' ' . $customer->last_name),
                                    ])
                                    ->filter()
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $customer = Customer::with('address')->find($state);
                                if ($customer) {
                                    $address = $customer->address->first();
                                    $set('address_choice', $address?->address_line_1 ?? '');
                                    $set('postal_code', $address?->postal_code ?? '');
                                    $set('city', $address?->city ?? '');
                                    $set('province', $address?->province ?? '');
                                }
                            }),

                        Select::make('address_choice')
                            ->label('Address')
                            ->options(function (Get $get) {
                                $customer = Customer::with('address')->find($get('customerID'));
                                if ($customer) {
                                    return $customer->address
                                        ->pluck('address_line_1', 'address_line_1')
                                        ->merge($customer->address->pluck('address_line_2', 'address_line_2'))
                                        ->filter()
                                        ->toArray();
                                }
                                return [];
                            })
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $customer = Customer::with('address')->find($get('customerID'));
                                if ($customer) {
                                    $address = $customer->address->firstWhere('address_line_1', $state)
                                        ?? $customer->address->firstWhere('address_line_2', $state);
                                    $set('postal_code', $address?->postal_code ?? '');
                                    $set('city', $address?->city ?? '');
                                    $set('province', $address?->province ?? '');
                                }
                            }),

                        TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->required()
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('city')
                            ->label('City')
                            ->required()
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('province')
                            ->label('Province')
                            ->required()
                            ->disabled()
                            ->dehydrated(true),

                        DateTimePicker::make('order_date')
                            ->default(now())
                            ->required(),
                    ])->columns(2),

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
                                Log::info("Discount selection changed", ['discountID' => $state]);
        
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
                                                Log::info("Processing discount for item {$index}", [
                                                    'productID' => $item['productID'],
                                                    'discount_found' => $discount ? true : false,
                                                    'product_has_discount' => $discount ? $product->discounts->contains($discount) : false
                                                ]);
                        
                                                if ($discount && $product->discounts->contains($discount)) {
                                                    $finalPrice = $discount->getFinalPrice($originalPrice);
                                                    $discountName = $discount->name;
                                                    $discountAmount = $originalPrice - $finalPrice;
                            
                                                    Log::info("Applying discount to item {$index}", [
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
                    ->schema([
                        Select::make('payment_methodID')
                            ->label('Payment Method')
                            ->options(function () {
                                return PaymentMethod::where('method_name', '!=', 'Cash')
                                ->pluck('method_name', 'payment_methodID');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->afterStateHydrated(function (Set $set, $state, $record) {
                                if ($record?->payment) {
                                    $set('payment_methodID', $record->payment->payment_methodID);
                                }
                            }),

                        Select::make('status')
                            ->label('Payment Status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'verified' => 'Verified',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->required()
                            ->afterStateHydrated(function (Set $set, $state, $record) {
                                if ($record?->payment) {
                                    $set('status', $record->payment->status);
                                } else {
                                    $set('status', 'unpaid');
                                }
                            }),

                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->unique(table: 'payments', column: 'reference_number')
                            ->afterStateHydrated(function (Set $set, $state, $record) {
                                if ($record?->payment) {
                                    $set('reference_number', $record->payment->reference_number);
                                }
                            }),

                        TextInput::make('downpayment')
                            ->label('Amount to Pay / Down Payment')
                            ->numeric()
                            ->required()
                            ->afterStateHydrated(function (Set $set, $state, $record) {
                                if ($record?->payment) {
                                    $set('downpayment', $record->payment->amount);
                                } else {
                                    $set('downpayment', 0);
                                }
                            }),
                    ])->columns(2)
                    ->columnSpanFull(),
                
                Section::make('Order Information')
                    ->schema([
                        Select::make('shipping_method')
                            ->label('Shipping Method')
                            ->options([
                                //'pickup' => 'Pickup',
                                'jnt' => 'JNT',
                                'lalamove' => 'Lalamove',
                            ])
                            ->required()
                            ->default('jnt')
                            ->afterStateHydrated(function (Set $set, $state, $record) {
                            // Ensure the correct data is retrieved
                            if ($record?->shipping) {
                                $set('shipping_method', $record->shipping->shipping_method);
                            }
                        }),

                        ToggleButtons::make('shipping_status')
                            ->label('Shipping Status')
                            ->inline()
                            ->default('processing')
                            ->options([
                                'processing' => 'Processing',
                                'in-transit' => 'In Transit',
                                'delivered' => 'Delivered',
                            ])
                            ->colors([
                                'processing' => 'warning',
                                'in-transit' => 'info',
                                'delivered' => 'success',
                            ])
                            ->icons([
                                'processing' => 'heroicon-m-arrow-path',
                                'in-transit' => 'heroicon-m-truck',
                                'delivered' => 'heroicon-m-check-badge',
                            ])
                            ->afterStateHydrated(function (Set $set, $state, $record) {
                                if ($record?->shipping) {
                                    $set('shipping_status', $record->shipping->shipping_status);
                                } else {
                                    $set('shipping_status', 'processing');
                                }
                            }),

                        ToggleButtons::make('order_status')
                            ->label('Order Status')
                            ->inline()
                            ->default('pending')
                            ->options([
                                'pending' => 'Pending',
                                'pre-order' => 'Pre-Order',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'returned' => 'Returned',
                            ])
                            ->colors([
                                'pending' => 'warning',
                                'processing' => 'info',
                                'pre-order' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'returned' => 'danger',
                            ])
                            ->icons([
                                'pending' => 'heroicon-m-sparkles',
                                'processing' => 'heroicon-m-truck',
                                'pre-order' => 'heroicon-m-arrow-path',
                                'completed' => 'heroicon-m-check-badge',
                                'cancelled' => 'heroicon-m-x-mark',
                                'returned' => 'heroicon-m-x-circle',
                            ])->columnSpanFull(),
                    ])->columns(2),

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
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('orderID', 'desc')
            ->columns([
                TextColumn::make('orderID')
                    ->label('Order ID')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('customer.first_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $record->customer?->first_name),

                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable()
                    ->money('PHP'),

                TextColumn::make('payment.paymentMethod.method_name')
                    ->label('Payment Method')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? 'N/A'),

                TextColumn::make('payment.status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid', 'verified' => 'success',
                        'failed' => 'danger',
                        'unpaid' => 'warning',
                        'completed' => 'success',
                        //'unpaid', 'verified', 'completed', 'failed'
                    })
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? 'N/A'),

                TextColumn::make('payment.reference_number')
                    ->label('Reference No.')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? 'N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('order_status')
                    ->label('Order Status')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'pre-order' => 'info',
                        //'pending', 'processing', 'completed', 'cancelled', 'pre-order'
                    }),

                TextColumn::make('shipping.shipping_status')
                    ->label('Shipping Status')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'warning',
                        'in-transit' => 'info',
                        'delivered' => 'success',
                    })
                    ->formatStateUsing(fn ($state) => $state ?? 'N/A'),

                TextColumn::make('order_date')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')->label('Archived Date')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::query()->where('order_status', 'pending')->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'manager', 'cashier']);
    }

}
