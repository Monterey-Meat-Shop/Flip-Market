<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\customer;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
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
                    ->dehydrateStateUsing(fn (Get $get) => collect($get('orderItems') ?? [])->sum('sub_total'))
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
                                            $set('unit_price', $product->price);
                                            $set('sub_total', $product->price * $get('quantity'));
                                        } else {
                                            $set('unit_price', 0);
                                            $set('sub_total', 0);
                                        }
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
                                        $set('sub_total', ($unitPrice && $state) ? $unitPrice * $state : 0);
                                    })
                                    ->rules([
                                        function (Get $get) {
                                            return function (string $attribute, $value, Closure $fail) use ($get) {
                                                $variant = ProductVariant::find($get('product_variant_id'));
                                                if ($variant && $value > $variant->stock_quantity) {
                                                    $fail("Quantity cannot exceed available stock ({$variant->stock_quantity}).");
                                                }
                                            };
                                        },
                                    ])
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

                Section::make('Total Amount of Order')
                    ->schema([
                        Placeholder::make('total_amount_placeholder')
                            ->label('Total Order Amount')
                            ->content(fn (Get $get) => number_format(collect($get('orderItems'))->pluck('sub_total')->sum(), 2))
                            ->live(),
                    ])->columns(2),
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
