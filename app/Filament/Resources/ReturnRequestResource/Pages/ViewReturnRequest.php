<?php

namespace App\Filament\Resources\ReturnRequestResource\Pages;

use App\Filament\Resources\ReturnRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;

class ViewReturnRequest extends ViewRecord
{
    protected static string $resource = ReturnRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('approve')
                ->label('Approve Return')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->return_status === 'pending')
                ->action(function () {
                    $this->record->update([
                        'return_status' => 'approved',
                        'approved_at' => now(),
                    ]);
                    
                    $this->record->order->update([
                        'order_status' => 'return_requested',
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Return Approved')
                        ->success()
                        ->send();
                }),
            
            Actions\Action::make('reject')
                ->label('Reject Return')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->return_status === 'pending')
                ->form([
                    \Filament\Forms\Components\Textarea::make('admin_notes')
                        ->label('Rejection Reason')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'return_status' => 'rejected',
                        'rejected_at' => now(),
                        'admin_notes' => $data['admin_notes'],
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Return Rejected')
                        ->danger()
                        ->send();
                }),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Return Request Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // TextEntry::make('returnID')
                                //    ->label('Return ID'),
                                
                                TextEntry::make('return_status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'rejected' => 'danger',
                                        'completed' => 'success',
                                        // 'refunded' => 'primary',
                                        default => 'gray',
                                    }),
                                
                                // TextEntry::make('order.orderID')
                                //    ->label('Order ID')
                                //    ->url(fn ($record) => route('filament.admin.resources.orders.view', $record->order)),
                                
                                TextEntry::make('created_at')
                                    ->label('Requested Date')
                                    ->dateTime('M d, Y h:i A'),
                            ]),
                    ]),
                
                Section::make('Customer Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('customer.first_name')
                                    ->label('Customer Name')
                                    ->formatStateUsing(fn ($record) => $record->customer->first_name . ' ' . $record->customer->last_name),
                                
                                TextEntry::make('customer.phone')
                                    ->label('Phone'),
                                
                                TextEntry::make('customer.user.email')
                                    ->label('Email'),
                            ]),
                    ]),
                
                Section::make('Return Reason')
                    ->schema([
                        TextEntry::make('return_reason')
                            ->label('Reason')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'not_delivered' => 'Product Not Delivered',
                                'defective' => 'Defective or Damaged Product',
                                'changed_mind' => 'Changed Mind/Not as Expected',
                                'incorrect' => 'Incorrect Product Received',
                                'other' => 'Other Reason',
                                default => $state,
                            })
                            ->badge(),
                        
                        TextEntry::make('other_reason')
                            ->label('Additional Details')
                            ->visible(fn ($record) => $record->return_reason === 'other' && !empty($record->other_reason)),
                        
                        ImageEntry::make('product_image')
                            ->label('')
                            ->disk('public')
                            ->size(300),
                    ]),
                
                // Section::make('Order Details')
                //     ->schema([
                //         Grid::make(2)
                //             ->schema([
                //                 TextEntry::make('order.order_date')
                //                     ->label('Order Date')
                //                     ->dateTime('M d, Y'),

                //                 TextEntry::make('product_names')
                //                     ->label('Returned Products')
                //                     ->html(),
                                
                //                 // TextEntry::make('order.total_amount')
                //                 //     ->label('Total Amount')
                //                 //     ->money('PHP'),
                                
                //                 // TextEntry::make('order.final_amount')
                //                 //     ->label('Final Amount')
                //                 //     ->money('PHP'),
                                
                //                 TextEntry::make('order.payment_status')
                //                     ->label('Payment Status')
                //                     ->badge(),
                //             ]),
                //     ])
                //     // ->collapsed()
                //     ,

                Section::make('Returned Products')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextEntry::make('product_names')
                                    ->label('Returned Item(s)')
                                    ->html()
                                    ->formatStateUsing(fn ($record) => $record->product_names ?: '<span style="color:gray;">No returned products found.</span>'),
                            ]),
                    ]),
                
                Section::make('Admin Notes & Processing')
                    ->schema([
                        TextEntry::make('admin_notes')
                            ->label('Admin Notes')
                            ->placeholder('No notes added yet'),
                        
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('approved_at')
                                    ->label('Approved At')
                                    ->dateTime('M d, Y h:i A')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => !empty($record->approved_at)),
                                
                                TextEntry::make('rejected_at')
                                    ->label('Rejected At')
                                    ->dateTime('M d, Y h:i A')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => !empty($record->rejected_at)),
                                
                                TextEntry::make('completed_at')
                                    ->label('Completed At')
                                    ->dateTime('M d, Y h:i A')
                                    ->placeholder('-')
                                    ->visible(fn ($record) => !empty($record->completed_at)),
                            ]),
                    ])
                    ->collapsed(fn ($record) => empty($record->admin_notes)),
            ]);
    }
}