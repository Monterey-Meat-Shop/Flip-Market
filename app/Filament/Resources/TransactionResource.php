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
use Filament\Notifications\Notification;


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
                // Hidden fields for data storage
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

                // Main two-column layout
                Forms\Components\Grid::make(3)
                    ->schema([
                        // Left Column - Main Form (2/3 width)
                        Forms\Components\Group::make([
                            // Customer Information
                            Section::make('Customer Information')
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

                            // Product Selection
                Section::make('Add Products')
                 ->schema([

                    // --- Product & Variant Selection ---
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Select::make('temp_productID')
                                ->label('Select Product')
                                ->options(
                                    Product::whereNotIn('status', ['pre_order', 'out_of_stock'])
                                        ->pluck('name', 'productID')
                                                    ->toArray()
                                )
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function (Set $set) {
                                    $set('temp_variant_id', null);
                                    $set('temp_quantity', 1);
                                }),

                            Select::make('temp_variant_id')
                                ->label('Size')
                                ->options(function (Get $get): array {
                                    $productID = $get('temp_productID');

                                    if ($productID) {
                                        return ProductVariant::where('product_id', $productID)
                                            ->pluck('size', 'id')
                                            ->toArray();
                                    }

                                    return [];
                                })
                                ->visible(fn (Get $get) => filled($get('temp_productID')))
                                ->live()
                                ->required(),
                        ]),

        // --- Colorway Info (read-only) ---
        Placeholder::make('colorway_info')
            ->label('Colorway')
            ->content(function (Get $get) {
                $variantId = $get('temp_variant_id');

                if ($variantId) {
                    $variant = ProductVariant::find($variantId);
                    return $variant ? $variant->colorway : '-';
                }

                return '-';
            })
            ->visible(fn (Get $get) => filled($get('temp_variant_id')))
            ->columnSpanFull(),

        // --- Quantity, Stock & Add Button ---
        Forms\Components\Grid::make(3)
            ->schema([
                // Quantity
                TextInput::make('temp_quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->visible(fn (Get $get) => filled($get('temp_productID')))
                    ->rules([
                        function (Get $get) {
                            return function (string $attribute, $value, Closure $fail) use ($get) {
                                $variantId = $get('temp_variant_id');

                                if ($variantId) {
                                    $variant = ProductVariant::find($variantId);

                                    if ($variant && $value > $variant->stock_quantity) {
                                        $fail("Only {$variant->stock_quantity} items available in stock.");
                                    }
                                }
                            };
                        },
                    ]),

                // Stock Info
                Placeholder::make('stock_info')
                    ->label('Available Stock')
                    ->content(function (Get $get) {
                        $variantId = $get('temp_variant_id');

                        if ($variantId) {
                            $variant = ProductVariant::find($variantId);
                            return $variant ? "{$variant->stock_quantity} available" : '';
                        }

                        return '';
                    })
                    ->visible(fn (Get $get) => filled($get('temp_variant_id')))
                    ->live(),

                // Add to Cart Button
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('addToCart')
                        ->label('Add to Cart')
                        ->icon('heroicon-m-plus')
                        ->color('success')
                        ->size('lg')
                        ->visible(fn (Get $get) =>
                            filled($get('temp_productID')) && filled($get('temp_variant_id'))
                        )
                        ->action(function (Get $get, Set $set) {
                            $productID = $get('temp_productID');
                            $variantId = $get('temp_variant_id');
                            $quantity  = $get('temp_quantity') ?? 1;

                            if (!$productID || !$variantId || $quantity < 1) {
                                return;
                            }

                            $product = Product::find($productID);
                            $variant = ProductVariant::find($variantId);

                            if (!$product || !$variant) {
                                return;
                            }

                            // Check stock
                            if ($quantity > $variant->stock_quantity) {
                                \Filament\Notifications\Notification::make()
                                    ->title("Only {$variant->stock_quantity} items available in stock.")
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $orderItems    = $get('orderItems') ?? [];
                            $existingIndex = null;

                            foreach ($orderItems as $index => $item) {
                                if (
                                    isset($item['productID'], $item['product_variant_id'])
                                    && $item['productID'] == $productID
                                    && $item['product_variant_id'] == $variantId
                                ) {
                                    $existingIndex = $index;
                                    break;
                                }
                            }
                                                        // Calculate prices with discount
                                                        $originalPrice = $product->price;
                                                        $finalPrice = $originalPrice;
                                                        $discountName = null;
                                                        $discountAmount = 0;

                                                        $discountID = $get('discountID');
                                                        if ($discountID) {
                                                            $discount = Discount::find($discountID);
                                                            if ($discount && $product->discounts->contains($discount)) {
                                                                $finalPrice = $discount->getFinalPrice($originalPrice);
                                                                $discountName = $discount->name;
                                                                $discountAmount = $originalPrice - $finalPrice;
                                                            }
                                                        }

                                                        if ($existingIndex !== null) {
                                                            // Update existing item
                                                            $currentQty = $orderItems[$existingIndex]['quantity'] ?? 0;
                                                            $newQty = $currentQty + $quantity;
                                                            
                                                            // If newQty exceeds available stock, notify and do not update
                                                            if ($newQty > $variant->stock_quantity) {
                                                                \Filament\Notifications\Notification::make()
                                                                    ->title("Not enough stock. Only {$variant->stock_quantity} items available.")
                                                                    ->danger()
                                                                    ->send();
                                                                return;
                                                            }

                                                            if ($newQty <= $variant->stock_quantity) {
                                                                $set("orderItems.{$existingIndex}.quantity", $newQty);
                                                                $set("orderItems.{$existingIndex}.sub_total", $finalPrice * $newQty);
                                                            }
                                                        } else {
                                                            // Add new item
                                                            $newItem = [
                                                                'productID' => $productID,
                                                                'product_variant_id' => $variantId,
                                                                'size' => $variant->size,
                                                                'colorway' => $variant->colorway,
                                                                'quantity' => $quantity,
                                                                'original_price' => $originalPrice,
                                                                'discount_name' => $discountName,
                                                                'discount_amount' => $discountAmount,
                                                                'unit_price' => $finalPrice,
                                                                'sub_total' => $finalPrice * $quantity,
                                                            ];
                                                            
                                                            $orderItems[] = $newItem;
                                                            $set('orderItems', $orderItems);
                                                        }

                                                        // Clear form
                                                        $set('temp_productID', null);
                                                        $set('temp_variant_id', null);
                                                        $set('temp_quantity', 1);
                                                    }),
                                            ])
                                                ->alignEnd(),
                                        ]),
                                ])
                                ->collapsible(),

                            // Discount Section
                            Section::make('Discounts')
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
                                            // Recalculate all items when discount changes
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
                                                            if ($discount && $product->discounts->contains($discount)) {
                                                                $finalPrice = $discount->getFinalPrice($originalPrice);
                                                                $discountName = $discount->name;
                                                                $discountAmount = $originalPrice - $finalPrice;
                                                            }
                                                        }
                            
                                                        $quantity = $item['quantity'] ?? 1;
                            
                                                        $set("orderItems.{$index}.original_price", $originalPrice);
                                                        $set("orderItems.{$index}.discount_name", $discountName);
                                                        $set("orderItems.{$index}.discount_amount", $discountAmount);
                                                        $set("orderItems.{$index}.unit_price", $finalPrice);
                                                        $set("orderItems.{$index}.sub_total", $finalPrice * $quantity);
                                                    }
                                                }
                                            }
                                        }),
                                ])
                                ->collapsible(),

                            // Payment Information
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

                                //    TextInput::make('amount')
                                //     ->label('Amount')
                                //     ->numeric()
                                //     ->required()
                                //     ->default('0.00')
                                //     ->reactive()
                                //     ->afterStateHydrated(function ($state, callable $set) {
                                //         // Format when loading from DB
                                //         $set('amount', number_format((float) $state, 2, '.', ''));
                                //     })
                                //     ->afterStateUpdated(function ($state, callable $set) {
                                //         // Format only when the user finishes typing (on blur)
                                //         if ($state !== null && $state !== '') {
                                //             $set('amount', number_format((float) $state, 2, '.', ''));
                                //         } else {
                                //             $set('amount', '0.00');
                                //         }
                                //     })
                                //     ->live(onBlur: true), // <-- only triggers after leaving the field

                                //         TextInput::make('change')
                                //             ->label('Change')
                                //             ->numeric()
                                //             ->default('0.00')
                                //             ->disabled(), // read-only field
                                   
                                                    TextInput::make('amount')
    ->label('Amount')
    ->numeric()
    ->required()
    ->default('0.00')
    ->reactive()
    ->afterStateHydrated(function ($state, callable $set) {
        $set('amount', number_format((float) $state, 2, '.', ''));
    })
    ->afterStateUpdated(function ($state, callable $set, callable $get) {
        if ($state !== null && $state !== '') {
            $set('amount', number_format((float) $state, 2, '.', ''));
        } else {
            $set('amount', '0.00');
        }

        $amount   = (float) $get('amount');
        $subtotal = (float) $get('subtotal');

        // Live UI update for change
        if ($amount >= $subtotal) {
            $change = $amount - $subtotal;
            $set('change', number_format($change, 2, '.', ''));
        } else {
            $set('change', '0.00');
        }
    })
    ->live(onBlur: true)
    ->rule(function (callable $get) {
        return function (string $attribute, $value, $fail) use ($get) {
            $subtotal = (float) $get('subtotal');
            $amount   = (float) $value;

            if ($amount < $subtotal) {
                $fail('The transaction cannot proceed. Amount is less than the subtotal.');

                // Show popup too
                Notification::make()
                    ->title('Payment Error')
                    ->body('The transaction cannot proceed because the amount is not enough to cover the subtotal.')
                    ->danger()
                    ->send();
            }
        };
    }),

TextInput::make('change')
    ->label('Change')
    ->numeric()
    ->default('0.00')
    ->disabled(),



                                    Select::make('status')
                                        ->label('Payment Status')
                                        ->options([
                                            'unpaid' => 'Unpaid',
                                            'paid' => 'Paid',
                                            'verified' => 'Verified',
                                        ])
                                        ->default('paid')
                                        ->required()
                                        ->dehydrated(true),
                                ])
                                ->columns(2),
                        ])
                        ->columnSpan(2), // Takes 2/3 of the width

                        // Right Column - Cart Sidebar (1/3 width)
                        Forms\Components\Group::make([
                            Section::make('Shopping Cart')
                                ->schema([
                                    // Cart Items
                                    Repeater::make('orderItems')
                                        ->label('')
                                        //->relationship('orderItems')
                                        ->schema([
                                            Forms\Components\Grid::make(1)
                                                ->schema([
                                                    // Product Name
                                                    Placeholder::make('product_name')
                                                        ->content(function (Get $get) {
                                                            $productID = $get('productID');
                                                            if ($productID) {
                                                                $product = Product::find($productID);
                                                                return $product ? $product->name : 'Unknown Product';
                                                            }
                                                            return '';
                                                        })
                                                        ->extraAttributes(['class' => 'font-semibold']),

                                                    // Size and Colorway
                                                    Placeholder::make('variant_details')
                                                        ->content(function (Get $get) {
                                                            $size = $get('size');
                                                            $colorway = $get('colorway');
                                                            return "Size: {$size}" . ($colorway ? " • {$colorway}" : '');
                                                        })
                                                        ->extraAttributes(['class' => 'text-sm text-gray-600']),

                                                    // Quantity and Price
                                                    Forms\Components\Grid::make(2)
                                                        ->schema([
                                                            TextInput::make('quantity')
                                                                ->numeric()
                                                                ->minValue(1)
                                                                ->live()
                                                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                                    $unitPrice = $get('unit_price');
                                                                    $set('sub_total', ($unitPrice && $state) ? $unitPrice * $state : 0);
                                                                }),

                                                            Placeholder::make('price_display')
                                                                ->content(function (Get $get) {
                                                                    $subTotal = $get('sub_total') ?? 0;
                                                                    return '₱' . number_format($subTotal, 2);
                                                                })
                                                                ->live(),
                                                        ]),
                                                ]),

                                            // Hidden fields
                                            Hidden::make('productID')->dehydrated(true),
                                            Hidden::make('product_variant_id')->dehydrated(true),
                                            Hidden::make('size')->dehydrated(true),
                                            Hidden::make('colorway')->dehydrated(true),
                                            Hidden::make('unit_price')->dehydrated(true),
                                            Hidden::make('sub_total')->dehydrated(true),
                                            Hidden::make('original_price')->dehydrated(true),
                                            Hidden::make('discount_name')->dehydrated(true),
                                            Hidden::make('discount_amount')->dehydrated(true),
                                        ])
                                        ->addable(false)
                                        ->reorderable(false)
                                        ->collapsed(false)
                                        ->cloneable(false)
                                        ->defaultItems(0) // FIXED: Changed from 1 to 0 to prevent empty items
                                        ->itemLabel(fn (array $state): string => 
                                            Product::find($state['productID'] ?? null)?->name ?? 'Unknown Product'
                                        ),

                                    // Cart Summary
                                    Forms\Components\Grid::make(1)
                                        ->schema([
                                            Forms\Components\Fieldset::make('Order Summary')->schema([
                                                Placeholder::make('subtotal')
                                                    ->label('Subtotal')
                                                    ->content(function (Get $get) {
                                                        $subtotal = 0;
                                                        foreach ($get('orderItems') ?? [] as $item) {
                                                            $subtotal += ($item['original_price'] ?? 0) * ($item['quantity'] ?? 0);
                                                        }
                                                        return '₱' . number_format($subtotal, 2);
                                                    })
                                                    ->live(),

                                                Placeholder::make('discount')
                                                    ->label('Discount')
                                                    ->content(function (Get $get) {
                                                        $totalDiscount = 0;
                                                        foreach ($get('orderItems') ?? [] as $item) {
                                                            $totalDiscount += ($item['discount_amount'] ?? 0) * ($item['quantity'] ?? 0);
                                                        }
                                                        return $totalDiscount > 0
                                                            ? '-₱' . number_format($totalDiscount, 2)
                                                            : '₱0.00';
                                                    })
                                                    ->live()
                                                    ->extraAttributes(['class' => 'text-red-600']),

                                                Placeholder::make('total')
                                                    ->label('Total')
                                                    ->content(function (Get $get) {
                                                        $total = 0;
                                                        foreach ($get('orderItems') ?? [] as $item) {
                                                            $total += $item['sub_total'] ?? 0;
                                                        }
                                                        return '₱' . number_format($total, 2);
                                                    })
                                                    ->live()
                                                    ->extraAttributes(['class' => 'text-lg font-bold text-green-600']),
                                            ]),

                                            Select::make('order_status')
                                                ->label('Order Status')
                                                ->options([
                                                    'processing' => 'Processing',
                                                    'completed'  => 'Completed',
                                                ])
                                                ->default('completed')
                                                ->required(),
                                        ]),
                                ]),
                        ]),
                    ]),
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
