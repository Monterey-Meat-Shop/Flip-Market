<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput; 
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\ActionGroup;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('admin')) {
            return $query;
        }

        return $query->where('customerID', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('total_amount')
                    ->dehydrateStateUsing(fn (Get $get) => collect($get('orderItems') ?? [])->sum('sub_total'))
                    ->default(0.00),
                
                Forms\Components\Hidden::make('final_amount')
                    ->dehydrateStateUsing(fn (Get $get) => collect($get('orderItems') ?? [])->sum('sub_total'))
                    ->default(0.00),

                Section::make('Order Information')
                ->schema([
                    Select::make('customerID')
                        ->label('Customer Name')
                        ->relationship(name: 'customer', titleAttribute: 'first_name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    
                    DateTimePicker::make('order_date')
                        ->default(now())
                        ->required(),
                ])->columns(2),

                Section::make('Payment Information')
                    ->schema([
                        Select::make('payment_methodID')
                            ->label('Payment Method')
                            ->options(PaymentMethod::pluck('method_name', 'payment_methodID'))
                            ->required(),

                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->placeholder('Reference no.')
                            ->required(),
                        
                        Select::make('status')
                            ->label('Payment Status')
                            ->options([
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'pending' => 'Pending',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(3),

                Section::make('Order Items')
                ->schema([
                    Repeater::make('orderItems')
                        ->label('Order Items')
                        ->relationship('orderItems')
                        ->schema([
                            Select::make('productID')
                                ->relationship(name: 'product', titleAttribute: 'name')
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

                            TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->default(1)
                                ->minValue(1)
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    $unitPrice = $get('unit_price');
                                    if ($unitPrice && $state) {
                                        $set('sub_total', $unitPrice * $state);
                                    }
                                })
                                ->columnSpan(2),

                            TextInput::make('unit_price')
                                ->numeric()
                                ->required()
                                ->disabled()
                                ->dehydrated(true)
                                ->columnSpan(3),

                            TextInput::make('sub_total')
                                ->numeric()
                                ->required()
                                ->disabled()
                                ->dehydrated(true)
                                ->columnSpan(3),
                        ])->columns(12)
                        ->collapsible()
                        ->defaultItems(1)
                        ->live(),
                ]),
                
                Placeholder::make('total_amount_placeholder')
                    ->label('Total Order Amount')
                    ->content(function (Get $get) {
                        $subTotals = collect($get('orderItems'))
                            ->pluck('sub_total')
                            ->filter();
                        return number_format($subTotals->sum(), 2);
                    })
                    ->live(),
                
                Select::make('order_status')
                    ->label('Order Status')
                    ->options([
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->default('completed')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('orderID', 'desc')
            ->columns([
                TextColumn::make('customer.first_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable()
                    ->money('PHP'),

                TextColumn::make('payment.paymentMethod.method_name')
                    ->label('Payment Method')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('order_status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('payment.status')
                    ->label('Payment Status')
                    ->badge()
                    ->sortable(),

                // TextColumn::make('order_date')
                //     ->dateTime()
                //     ->sortable(),

                TextColumn::make('order_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
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
        return [
            //
        ];
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
