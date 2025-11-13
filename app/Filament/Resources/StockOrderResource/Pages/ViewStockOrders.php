<?php

namespace App\Filament\Resources\StockOrderResource\Pages;

use App\Filament\Resources\StockOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Placeholder;

class ViewStockOrder extends ViewRecord
{
    protected static string $resource = StockOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn ($record) => $record->status !== 'completed'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Order Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('purchase_order_number')
                                    ->label('PO Number')
                                    ->default('N/A'),

                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn ($state) => match($state) {
                                        'pending' => 'warning',
                                        'partial' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                    }),
                            ]),
                    ]),

                Section::make('Products in Order')
                    ->schema(fn ($record) => $record->stockItems->map(function ($item, $index) {
                        $productName = $item->product?->name ?? 'N/A';
                        $variantName = ($item->variant?->size ?? '') . ' - ' . ($item->variant?->colorway ?? '');
                        $currentStock = $item->variant?->stock_quantity ?? 0;

                        return Grid::make(4)->schema([
                            TextEntry::make("product_{$index}")
                                ->label('Product')
                                ->default($productName),

                            TextEntry::make("variant_{$index}")
                                ->label('Size / Variant')
                                ->default($variantName),

                            TextEntry::make("current_stock_{$index}")
                                ->label('Current Stock')
                                ->default($currentStock),

                            TextEntry::make("ordered_qty_{$index}")
                                ->label('Ordered Qty')
                                ->default($item->stock_quantity),
                        ]);
                    })->toArray()),
                
                // Latest requirement removed the quantity details section        

                // Section::make('Quantity Details')
                //     ->schema([
                //         Grid::make(3)
                //             ->schema([
                //                 TextEntry::make('ordered_quantity')
                //                     ->label('Total Ordered')
                //                     ->badge()
                //                     ->color('gray'),

                //                 TextEntry::make('received_quantity')
                //                     ->label('Received Quantity')
                //                     ->badge()
                //                     ->color('success'),

                //                 TextEntry::make('pending_quantity')
                //                     ->label('Pending Quantity')
                //                     ->badge()
                //                     ->color(fn ($record) => $record->pending_quantity > 0 ? 'warning' : 'success'),
                //             ]),
                //     ]),

                Section::make('Supplier & Dates')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('supplier_name')
                                    ->label('Supplier'),

                                TextEntry::make('estimated_delivery_date')
                                    ->label('Estimated Delivery')
                                    ->date('F d, Y')
                                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : 'gray'),

                                TextEntry::make('actual_delivery_date')
                                    ->label('Actual Delivery Date')
                                    ->date('F d, Y')
                                    ->default('Not yet delivered')
                                    ->visible(fn ($record) => $record->actual_delivery_date !== null),
                            ]),
                    ]),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->default('No notes')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('creator.name')
                                    ->label('Created By'),

                                TextEntry::make('created_at')
                                    ->label('Created On')
                                    ->dateTime('F d, Y h:i A'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('F d, Y h:i A'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
