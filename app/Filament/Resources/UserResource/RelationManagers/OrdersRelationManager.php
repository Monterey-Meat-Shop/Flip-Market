<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('orderID')
            ->columns([
                // TextColumn::make('orderID')
                //     ->label('Order ID')
                //     ->sortable()
                //     ->searchable(),

                TextColumn::make('customer.first_name')
                    ->label('Customer')
                    ->formatStateUsing(fn($record) =>
                        $record->customer?->first_name . ' ' . $record->customer?->last_name
                    )
                    ->sortable(),

                // TextColumn::make('payment.payment_status')
                //     ->label('Payment')
                //     ->badge()
                //     ->colors([
                //         'secondary' => 'unpaid',
                //         'success' => 'paid',
                //         'danger' => 'failed',
                //     ])
                //     ->sortable(),

                // TextColumn::make('sub_total')
                //     ->label('Subtotal')
                //     ->money('PHP')
                //     ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PHP')
                    ->sortable(),

                BadgeColumn::make('order_status')
                    ->label('Order Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
               // Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}