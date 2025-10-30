<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Shipping;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingWidget extends BaseWidget
{
    protected static ?string $heading = 'Pending Orders';
    public static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query())
            ->columns([
                TextColumn::make('orderID')->label('Order ID')->sortable(),
                TextColumn::make('shipping.shipping_status')->label('Shipping Status')->badge(),
                TextColumn::make('payment_status')->label('Payment Status')->badge(),
                TextColumn::make('order_status')->label('Order Status')->badge(),
                TextColumn::make('total_amount')->label('Total')->money('php')->color('success'),
                TextColumn::make('order_date')
                    ->label('Order Date')
                    ->dateTime('M d, Y H:i'),
            ])
            ->filters([
                SelectFilter::make('order_status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])->default('pending'),
            ])
            
            ->defaultSort('order_date', 'desc');
    }
}
 