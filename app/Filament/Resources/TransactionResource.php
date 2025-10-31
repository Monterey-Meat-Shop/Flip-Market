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
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole(['admin', 'cashier'])) {
            return $query;
        }

        if ($user->hasRole('cashier')) {
            $guestUser = User::where('first_name', 'guest')->first();
            if ($guestUser) {
                return $query->where('customerID', $guestUser->id);
            }
            return $query->whereRaw('1 = 0');
        }

        return $query->where('customerID', $user->id);
    }

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();
        return ($user && $user->hasRole('admin')) ? 'Sales' : null;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole(['admin', 'cashier']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['admin', 'cashier']);
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
        return $form->schema([
            Hidden::make('total_amount')->default(0.00),
            Hidden::make('final_amount')->default(0.00),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Group::make([

                    // ================= CUSTOMER INFO =================
                    Section::make('Customer Information')->schema([
                        Select::make('customerID')
                            ->label('Customer Name')
                            ->relationship(
                                name: 'customer',
                                titleAttribute: 'first_name',
                                modifyQueryUsing: fn(Builder $query) =>
                                $query->where('first_name', 'guest')->orderBy('first_name')
                            )
                            ->getOptionLabelFromRecordUsing(fn(Model $record) =>
                                "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->required(),

                        DateTimePicker::make('order_date')
                            ->default(now())
                            ->required()
                            ->disabled()
                            ->dehydrated(true),
                    ])->columns(2),

                    // ================= ADD PRODUCTS =================
                    Section::make('Add Products')->schema([
                        Forms\Components\Grid::make(2)->schema([
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
                                ->visible(fn(Get $get) => filled($get('temp_productID')))
                                ->live()
                                ->required(),
                        ]),

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
                            ->visible(fn(Get $get) => filled($get('temp_variant_id')))
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(3)->schema([
                            TextInput::make('temp_quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->visible(fn(Get $get) => filled($get('temp_productID'))),

                            Placeholder::make('stock_info')
                                ->label('Available Stock')
                                ->content(function (Get $get) {
                                    $variantId = $get('temp_variant_id');
                                    if ($variantId) {
                                        $variant = ProductVariant::find($variantId);
                                        $stock = $variant ? $variant->stock_quantity : 0;

                                        if ($stock <= 0) {
                                            return new \Illuminate\Support\HtmlString(
                                                '<span class="text-red-600 font-semibold">Out of Stock</span>'
                                            );
                                        } elseif ($stock <= 5) {
                                            return new \Illuminate\Support\HtmlString(
                                                '<span class="text-orange-600 font-semibold">' . $stock . ' available (Low Stock)</span>'
                                            );
                                        } else {
                                            return new \Illuminate\Support\HtmlString(
                                                '<span class="text-green-600 font-semibold">' . $stock . ' available</span>'
                                            );
                                        }
                                    }
                                    return '';
                                })
                                ->visible(fn(Get $get) => filled($get('temp_variant_id')))
                                ->live(),

                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('addToCart')
                                    ->label('Add to Cart')
                                    ->icon('heroicon-m-plus')
                                    ->color('success')
                                    ->size('lg')
                                    ->visible(fn(Get $get) =>
                                        filled($get('temp_productID')) && filled($get('temp_variant_id')))
                                    ->action(function (Get $get, Set $set) {
                                        $productID = $get('temp_productID');
                                        $variantId = $get('temp_variant_id');
                                        $quantity = $get('temp_quantity') ?? 1;

                                        if (!$productID || !$variantId || $quantity < 1) {
                                            return;
                                        }

                                        $product = Product::find($productID);
                                        $variant = ProductVariant::find($variantId);
                                        if (!$product || !$variant) {
                                            return;
                                        }

                                        // CHECK STOCK AVAILABILITY
                                        if ($variant->stock_quantity <= 0) {
                                            Notification::make()
                                                ->title('Out of Stock')
                                                ->body("Sorry, {$product->name} (Size: {$variant->size}) is currently out of stock.")
                                                ->danger()
                                                ->duration(5000)
                                                ->send();
                                            return;
                                        }

                                        // CHECK IF REQUESTED QUANTITY IS AVAILABLE
                                        if ($quantity > $variant->stock_quantity) {
                                            Notification::make()
                                                ->title('Insufficient Stock')
                                                ->body("Only {$variant->stock_quantity} units of {$product->name} (Size: {$variant->size}) are available.")
                                                ->warning()
                                                ->duration(5000)
                                                ->send();
                                            return;
                                        }

                                        $orderItems = $get('orderItems') ?? [];
                                        $existingIndex = null;

                                        // CHECK IF ITEM ALREADY EXISTS IN CART
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

                                        // CHECK STOCK WHEN ADDING TO EXISTING CART ITEM
                                        if ($existingIndex !== null) {
                                            $newTotalQuantity = $orderItems[$existingIndex]['quantity'] + $quantity;
                                            if ($newTotalQuantity > $variant->stock_quantity) {
                                                Notification::make()
                                                    ->title('Insufficient Stock')
                                                    ->body("Cannot add {$quantity} more units. You already have {$orderItems[$existingIndex]['quantity']} in cart. Only {$variant->stock_quantity} units available.")
                                                    ->warning()
                                                    ->duration(6000)
                                                    ->send();
                                                return;
                                            }
                                        }

                                        $originalPrice = $product->price;
                                        $finalPrice = $originalPrice;
                                        $discountName = null;
                                        $discountAmount = 0;
                                        $discountLabel = null;

                                        $discountID = $get('discountID');
                                        if ($discountID) {
                                            $discount = Discount::find($discountID);
                                            if ($discount && $product->discounts->contains($discount)) {
                                                $finalPrice = $discount->getFinalPrice($originalPrice);
                                                $discountName = $discount->name;
                                                $discountAmount = $originalPrice - $finalPrice;
                                                $discountLabel = $discount->type === 'percentage'
                                                    ? "{$discountName} ({$discount->value}%)"
                                                    : "{$discountName} (₱{$discount->value} OFF)";
                                            }
                                        }

                                        if ($existingIndex !== null) {
                                            $orderItems[$existingIndex]['quantity'] += $quantity;
                                            $orderItems[$existingIndex]['sub_total'] =
                                                $finalPrice * $orderItems[$existingIndex]['quantity'];
                                            $orderItems[$existingIndex]['discount_label'] = $discountLabel;
                                        } else {
                                            $orderItems[] = [
                                                'productID' => $productID,
                                                'product_variant_id' => $variantId,
                                                'size' => $variant->size,
                                                'colorway' => $variant->colorway,
                                                'quantity' => $quantity,
                                                'original_price' => $originalPrice,
                                                'discount_name' => $discountName,
                                                'discount_amount' => $discountAmount,
                                                'discount_label' => $discountLabel,
                                                'unit_price' => $finalPrice,
                                                'sub_total' => $finalPrice * $quantity,
                                            ];
                                        }

                                        $set('orderItems', $orderItems);

                                        // Sync totals
                                        $total = collect($orderItems)->sum('sub_total');
                                        $set('total_amount', $total);
                                        $set('final_amount', $total);
                                        $set('payment.amount', number_format($total, 2, '.', ''));

                                        // SUCCESS NOTIFICATION
                                        Notification::make()
                                            ->title('Item Added Successfully')
                                            ->body("{$quantity} × {$product->name} (Size: {$variant->size}) added to cart.")
                                            ->success()
                                            ->duration(3000)
                                            ->send();

                                        $set('temp_productID', null);
                                        $set('temp_variant_id', null);
                                        $set('temp_quantity', 1);
                                    }),
                            ])->alignEnd(),
                        ]),
                    ])->collapsible(),

                    // ================= DISCOUNTS =================
                    Section::make('Discounts')->schema([
                        Select::make('discountID')
                            ->label('Apply Discount')
                            ->options(
                                fn(Get $get): array =>
                                Discount::where('is_active', true)
                                    ->pluck('name', 'discountID')
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $orderItems = $get('orderItems') ?? [];
                                foreach ($orderItems as $index => $item) {
                                    $product = Product::find($item['productID']);
                                    if (!$product)
                                        continue;

                                    $originalPrice = $product->price;
                                    $finalPrice = $originalPrice;
                                    $discountName = null;
                                    $discountAmount = 0;
                                    $discountLabel = null;

                                    if ($state) {
                                        $discount = Discount::find($state);
                                        if ($discount && $product->discounts->contains($discount)) {
                                            $finalPrice = $discount->getFinalPrice($originalPrice);
                                            $discountName = $discount->name;
                                            $discountAmount = $originalPrice - $finalPrice;
                                            $discountLabel = $discount->type === 'percentage'
                                                ? "{$discountName} ({$discount->value}%)"
                                                : "{$discountName} (₱{$discount->value} OFF)";
                                        }
                                    }

                                    $orderItems[$index]['original_price'] = $originalPrice;
                                    $orderItems[$index]['discount_name'] = $discountName;
                                    $orderItems[$index]['discount_amount'] = $discountAmount;
                                    $orderItems[$index]['discount_label'] = $discountLabel;
                                    $orderItems[$index]['unit_price'] = $finalPrice;
                                    $orderItems[$index]['sub_total'] = $finalPrice * $item['quantity'];
                                }

                                // Sync totals
                                $total = collect($orderItems)->sum('sub_total');
                                $set('orderItems', $orderItems);
                                $set('total_amount', $total);
                                $set('final_amount', $total);
                                $set('payment.amount', number_format($total, 2, '.', ''));
                            }),
                    ])->collapsible(),

                    // ================= PAYMENT =================
                    Section::make('Payment Information')
                        ->relationship('payment')
                        ->schema([
                            Select::make('payment_methodID')
                                ->label('Payment Method')
                                ->relationship(
                                    name: 'paymentMethod',
                                    titleAttribute: 'method_name',
                                    modifyQueryUsing: fn(Builder $query) =>
                                    $query->whereIn('method_name', ['Cash', 'GCash'])
                                )
                                ->required()
                                ->preload()
                                ->dehydrated(true)
                                ->live()
                                ->afterStateUpdated(function (Set $set, $state) {
                                    $paymentMethod = PaymentMethod::find($state);
                                    if (!$paymentMethod || $paymentMethod->method_name !== 'GCash') {
                                        $set('reference_number', null);
                                    }
                                }),

                            // STATIC QR CODE DISPLAY - Shows when GCash is selected
                            // ✅ QR Code Display and Reference Number – synchronized visibility
                            Placeholder::make('gcash_qr_display')
                                ->label('Scan to Pay')
                                ->content(function () {
                                    $qrUrl = asset('images/gshak.png'); // path to your uploaded QR
                                    return new \Illuminate\Support\HtmlString('
            <div class="flex flex-col items-center gap-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <img 
                    src="' . $qrUrl . '" 
                    alt="GCash QR Code" 
                    class="w-32 h-32 md:w-48 md:h-48 object-contain rounded-lg shadow-lg cursor-pointer transition-transform hover:scale-105"
                    onclick="
                        const modal = document.createElement(\'div\');
                        modal.className = \'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 cursor-pointer\';
                        modal.onclick = () => modal.remove();
                        modal.innerHTML = \'<img src=\\\'' . $qrUrl . '\\\' class=\\\'max-w-2xl max-h-screen rounded-lg shadow-2xl\\\'>\';
                        document.body.appendChild(modal);

                        // Enable ESC to close
                        const closeOnEsc = (e) => {
                            if (e.key === \'Escape\') {
                                modal.remove();
                                document.removeEventListener(\'keydown\', closeOnEsc);
                            }
                        };
                        document.addEventListener(\'keydown\', closeOnEsc);
                    "
                    title="Click to enlarge"
                />
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center font-medium">
                    Click image to enlarge<br>
                    Scan this QR code with GCash app
                </p>
            </div>
        ');
                                })
                                ->visible(function (Get $get) {
                                    $paymentMethodId = $get('payment_methodID');
                                    if (!$paymentMethodId)
                                        return false;
                                    $paymentMethod = \App\Models\PaymentMethod::find($paymentMethodId);
                                    return $paymentMethod && strtolower($paymentMethod->method_name) === 'gcash';
                                })
                                ->columnSpanFull(),

                            TextInput::make('reference_number')
                                ->label('Reference Number')
                                ->placeholder('Enter GCash reference number')
                                ->visible(function (Get $get) {
                                    $paymentMethodId = $get('payment_methodID');
                                    if (!$paymentMethodId)
                                        return false;
                                    $paymentMethod = \App\Models\PaymentMethod::find($paymentMethodId);
                                    return $paymentMethod && strtolower($paymentMethod->method_name) === 'gcash';
                                })
                                ->required(function (Get $get) {
                                    $paymentMethodId = $get('payment_methodID');
                                    if (!$paymentMethodId)
                                        return false;
                                    $paymentMethod = \App\Models\PaymentMethod::find($paymentMethodId);
                                    return $paymentMethod && strtolower($paymentMethod->method_name) === 'gcash';
                                })
                                ->dehydrated(true)
                                ->live()
                                ->columnSpanFull(),


                            TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->required()
                                ->prefix('₱')
                                ->disabled()
                                ->dehydrated(true)
                                ->afterStateHydrated(
                                    fn($component, $state, Get $get) =>
                                    $component->state(number_format($get('final_amount') ?? 0, 2, '.', ''))
                                ),

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
                        ])->columns(2),
                ])->columnSpan(2),

                // ================= CART =================
                Forms\Components\Group::make([
                    Section::make('Shopping Cart')->schema([
                        Repeater::make('orderItems')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Placeholder::make('product_name')
                                        ->content(
                                            fn(Get $get) =>
                                            Product::find($get('productID'))?->name ?? 'Unknown'
                                        )
                                        ->extraAttributes(['class' => 'font-semibold']),

                                    Placeholder::make('variant_details')
                                        ->content(
                                            fn(Get $get) =>
                                            "Size: {$get('size')} • {$get('colorway')}"
                                        )
                                        ->extraAttributes(['class' => 'text-sm text-gray-600']),
                                ]),

                                // Show discount info
                                Placeholder::make('discount_info')
                                    ->content(
                                        fn(Get $get) =>
                                        $get('discount_label')
                                        ? "Discount: " . $get('discount_label')
                                        : "No Discount"
                                    )
                                    ->extraAttributes(['class' => 'text-sm text-blue-500 font-medium'])
                                    ->columnSpanFull(),

                                TextInput::make('quantity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        // Validate stock when quantity is changed
                                        $variantId = $get('product_variant_id');
                                        if ($variantId) {
                                            $variant = ProductVariant::find($variantId);
                                            if ($variant && $state > $variant->stock_quantity) {
                                                Notification::make()
                                                    ->title('Insufficient Stock')
                                                    ->body("Only {$variant->stock_quantity} units available.")
                                                    ->warning()
                                                    ->duration(4000)
                                                    ->send();

                                                $set('quantity', $variant->stock_quantity);
                                                $state = $variant->stock_quantity;
                                            }
                                        }

                                        $unitPrice = $get('unit_price');
                                        $set('sub_total', ($unitPrice && $state) ? $unitPrice * $state : 0);

                                        // Sync totals after quantity change
                                        $total = collect($get('../../orderItems') ?? [])->sum('sub_total');
                                        $set('../../total_amount', $total);
                                        $set('../../final_amount', $total);
                                        $set('../../payment.amount', number_format($total, 2, '.', ''));
                                    }),

                                Placeholder::make('price_display')
                                    ->content(
                                        fn(Get $get) =>
                                        '₱' . number_format($get('sub_total') ?? 0, 2)
                                    )
                                    ->live(),

                                Hidden::make('productID'),
                                Hidden::make('product_variant_id'),
                                Hidden::make('size'),
                                Hidden::make('colorway'),
                                Hidden::make('unit_price'),
                                Hidden::make('sub_total'),
                                Hidden::make('original_price'),
                                Hidden::make('discount_name'),
                                Hidden::make('discount_amount'),
                                Hidden::make('discount_label'),
                            ])
                            ->addable(false)
                            ->reorderable(false)
                            ->collapsed(false)
                            ->cloneable(false)
                            ->defaultItems(0)
                            ->deletable(true)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // This fires when items are deleted from the repeater
                                // $state contains the updated orderItems array after deletion
                                $total = collect($state ?? [])->sum('sub_total');

                                $set('total_amount', $total);
                                $set('final_amount', $total);
                                $set('payment.amount', number_format($total, 2, '.', ''));
                            })
                            ->live(),

                        Forms\Components\Fieldset::make('Order Summary')->schema([
                            Placeholder::make('subtotal')
                                ->label('Subtotal')
                                ->content(
                                    fn(Get $get) =>
                                    '₱' . number_format(collect($get('orderItems') ?? [])
                                        ->sum(fn($i) => ($i['original_price'] ?? 0) * ($i['quantity'] ?? 0)), 2)
                                )
                                ->live(),

                            Placeholder::make('discount')
                                ->label('Discount')
                                ->content(
                                    fn(Get $get) =>
                                    '-₱' . number_format(collect($get('orderItems') ?? [])
                                        ->sum(fn($i) => ($i['discount_amount'] ?? 0) * ($i['quantity'] ?? 0)), 2)
                                )
                                ->live(),

                            Placeholder::make('total')
                                ->label('Total')
                                ->content(
                                    fn(Get $get) =>
                                    '₱' . number_format($get('final_amount') ?? 0, 2)
                                )
                                ->live()
                                ->extraAttributes(['class' => 'text-lg font-bold text-green-600']),
                        ]),
                    ]),
                ]),
            ]),
        ]);
    }

    // Helper method to sync totals
    protected static function syncTotals(Set $set, Get $get): void
    {
        $orderItems = $get('../../orderItems') ?? [];
        $total = collect($orderItems)->sum('sub_total');

        $set('../../total_amount', $total);
        $set('../../final_amount', $total);
        $set('../../payment.amount', number_format($total, 2, '.', ''));
    }

    // Helper method to sync totals after item deletion
    protected static function syncTotalsAfterDelete(Set $set, Get $get, $state): void
    {
        // $state contains the updated orderItems array after deletion
        $total = collect($state ?? [])->sum('sub_total');

        $set('total_amount', $total);
        $set('final_amount', $total);
        $set('payment.amount', number_format($total, 2, '.', ''));
    }

    // ================= TABLE =================
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('orderID', 'desc')
            ->columns([
                TextColumn::make('customer.first_name')->label('Customer Name'),
                TextColumn::make('orderItems.product.name')->label('Products')->listWithLineBreaks(),
                TextColumn::make('payment_reference')
                    ->label('Payment Method')
                    ->getStateUsing(function ($record) {
                        $method = $record->payment?->paymentMethod?->method_name ?? '-';
                        $reference = $record->payment?->reference_number ?? null;

                        // Show "GCash/ReferenceNumber" only if method is GCash
                        if (strtolower($method) === 'gcash' && $reference) {
                            return "{$method}/{$reference}";
                        }

                        return $method; // Otherwise, just show the method name (e.g., Cash)
                    }),

                TextColumn::make('payment.amount')
                    ->label('Paid Amount')
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('payment.change')
                    ->label('Change')
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_amount')->label('Total Amount')->money('PHP'),
                TextColumn::make('order_status')
                ->label('Order Status')
                ->badge()
                ->colors([
                    'success' => 'completed',     // ✅ Green for completed
                    'warning' => 'pending',       // 🟡 Yellow for pending (optional)
                    'danger'  => 'cancelled',     // 🔴 Red for cancelled (optional)
                    'gray'    => 'processing',    // ⚪ Gray for processing (optional)
                ]),

                TextColumn::make('created_at')->label('Order Date')->dateTime(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                    // Print action
                    Action::make('print')
                        ->label('Print')
                        ->icon('heroicon-o-printer')
                        ->url(fn ($record) => route('filament.transactions.print', $record))
                        ->openUrlInNewTab()
                        ->color('secondary'),
                ])
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
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
}