<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockOrderResource\Pages;
use App\Models\StockOrder;
use App\Models\StockOrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StockOrderResource extends Resource
{
    protected static ?string $model = StockOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    protected static ?string $navigationLabel = 'Stock Orders';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();
        
        if ($user && $user->hasRole('admin')) {
            return 'Products';
        }
        return null;
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole('admin') && $record->status !== 'completed';
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('stock_order_details')
                    ->schema([
                        Forms\Components\Repeater::make('stockItems')
                            ->label('Products to Order')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::query()->pluck('name', 'productID'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('product_variant_id', null);
                                    })
                                    ->preload()
                                    ->columnSpan(1),

                                Forms\Components\Select::make('product_variant_id')
                                    ->label('Size / Variant')
                                    ->options(function (Get $get) {
                                        $productId = $get('product_id');
                                        if (!$productId) return [];
                                        return ProductVariant::where('product_id', $productId)
                                            ->get()
                                            ->mapWithKeys(fn ($variant) => [
                                                $variant->id => "Size {$variant->size} - {$variant->colorway} (Stock: {$variant->stock_quantity})"
                                            ])
                                            ->toArray(); // <- important
                                    })
                                    ->searchable()
                                    ->required()
                                    ->disabled(fn (Get $get) => !$get('product_id'))
                                    ->preload(false) // disable preload
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('stock_quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->suffix('pcs')
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Add Another Product')
                            ->reorderable(false)
                            ->collapsible()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('supplier_name')
                            ->label('Supplier Name/Business Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('purchase_order_number')
                            ->label('Purchase Order Number')
                            ->maxLength(255)
                            ->helperText('Optional: PO# for reference'),

                        Forms\Components\DatePicker::make('estimated_delivery_date')
                            ->label('Estimated Delivery Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('M d, Y')
                            ->minDate(now())
                            ->helperText('Expected arrival date at store'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Any additional information about this order')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase_order_number')
                    ->label('PO#')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),

        //         TextColumn::make('productName')
        //     ->label('Product')
        //     ->sortable()
        //     ->searchable(),

        // TextColumn::make('variantSize')
        //     ->label('Size'),

        // TextColumn::make('variantColor')
        //     ->label('Colorway'),


                TextColumn::make('pending_quantity')
                    ->label('Pending')
                    ->alignCenter()
                    ->sortable()
                    ->color(fn ($record) => $record->pending_quantity > 0 ? 'warning' : 'success'),

                TextColumn::make('received_quantity')
                    ->label('Received')
                    ->alignCenter()
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->stockItems->sum('stock_quantity_received')),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'partial',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-arrow-path' => 'partial',
                        'heroicon-o-check-circle' => 'completed',
                        'heroicon-o-x-circle' => 'cancelled',
                    ])
                    ->sortable(),

                TextColumn::make('estimated_delivery_date')
                    ->label('ETA')
                    ->date('M d, Y')
                    ->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : ($record->isDeliveryApproaching() ? 'warning' : 'gray')),

                TextColumn::make('actual_delivery_date')
                    ->label('Delivered On')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->getStateUsing(fn ($record) => $record->creator?->name ?? 'N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('estimated_delivery_date', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending'),

                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Orders')
                    ->query(fn (Builder $query) => $query
                        ->where('estimated_delivery_date', '<', now())
                        ->whereIn('status', ['pending', 'partial'])
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('approaching')
                    ->label('Delivery Soon (3 days)')
                    ->query(fn (Builder $query) => $query
                        ->whereBetween('estimated_delivery_date', [
                            now()->subDays(3),
                            now()->addDays(3)
                        ])
                        ->whereIn('status', ['pending', 'partial'])
                    )
                    ->toggle(),
            ])
            ->actions([
                Action::make('accept_stock')
                   ->label('Accept Stock')
                   ->icon('heroicon-o-check-circle')
                   ->color('success')
                   ->visible(fn ($record) => $record->pending_quantity > 0 && in_array($record->status, ['pending', 'partial']))
                   ->form([
                       Forms\Components\Placeholder::make('info')
                           ->content(fn ($record) => "Order: " . ($record->purchase_order_number ?? 'N/A') . " | Pending: {$record->pending_quantity} units"),

                       Forms\Components\TextInput::make('quantity')
                           ->label('Quantity to Accept')
                           ->numeric()
                           ->required()
                           ->minValue(1)
                           ->maxValue(fn ($record) => $record->pending_quantity)
                           ->default(fn ($record) => $record->pending_quantity)
                           ->helperText(fn ($record) => "Maximum: {$record->pending_quantity} units")
                           // ->disabled()
                           ->readOnly(),

                       Forms\Components\Textarea::make('acceptance_notes')
                           ->label('Notes (Optional)')
                           ->rows(2)
                           ->placeholder('Any notes about this acceptance...'),
                   ])
                   ->action(function ($record, array $data) {
                   $quantity = (int) $data['quantity'];
                   $notes = $data['acceptance_notes'] ?? null;

                   $record->acceptStock($quantity, $notes);

                   // Reload stockItems with variant relationship
                   $record->load('stockItems.variant');

                   $updatedItems = $record->stockItems->filter(fn($item) => $item->stock_quantity_received > 0);

                   $variantList = $updatedItems->map(fn($item) => 
                       ($item->variant?->size ?? 'N/A') . ' - ' . ($item->variant?->colorway ?? 'N/A')
                   )->join(', ');

                   Notification::make()
                       ->title('Stock Accepted Successfully')
                       ->body("Added {$quantity} units to variant(s): {$variantList} inventory.")
                       ->success()
                       ->send();

                   if ($record->status === 'completed') {
                       Notification::make()
                           ->title('Stock Order Completed')
                           ->body("PO# {$record->purchase_order_number} is now fully received.")
                           ->success()
                           ->sendToDatabase(auth()->user());
                   }
               })
                ->requiresConfirmation()
                ->modalHeading('Accept Stock Delivery')
                ->modalDescription('This will add the specified quantity to the product variant inventory.')
                ->modalSubmitActionLabel('Accept Stock'),

                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    EditAction::make()
                        ->visible(fn ($record) => $record->status !== 'completed'),
                    
                    Action::make('cancel')
                        ->label('Cancel Order')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => in_array($record->status, ['pending', 'partial']))
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->status = 'cancelled';
                            $record->save();

                            Notification::make()
                                ->title('Stock Order Cancelled')
                                ->body("PO# {$record->purchase_order_number} has been cancelled.")
                                ->warning()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->visible(fn ($record) => $record->status === 'cancelled'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockOrders::route('/'),
            'create' => Pages\CreateStockOrder::route('/create'),
            'edit' => Pages\EditStockOrder::route('/{record}/edit'),
            'view' => Pages\ViewStockOrder::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product', 'productVariant', 'creator']);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereIn('status', ['pending', 'partial'])
            ->where('estimated_delivery_date', '<=', now()->addDays(7))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}