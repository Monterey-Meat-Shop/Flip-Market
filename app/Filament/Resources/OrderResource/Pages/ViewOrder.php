<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Components\TextEntry\TextEntryWeight;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->label('Archive')
                ->modalHeading('Archive Order')
                ->modalDescription('Are you sure you want to archive this order? You can restore it later if needed.')
                ->modalSubmitActionLabel('Archive') 
                ->modalCancelActionLabel('Cancel') 
                ->color('danger')
                ->icon('heroicon-o-archive-box')
                ->visible(fn () => in_array($this->record->order_status, ['completed', 'cancelled', 'returned'])),
                
            Actions\RestoreAction::make()
                ->visible(fn () => $this->record->trashed()),
                
            Actions\ForceDeleteAction::make()
                ->visible(fn () => $this->record->trashed()),

            Actions\Action::make('accept')
                ->label('Accept Order')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->order_status === 'pending')
                ->action(function () {
                    $shipping = $this->record->shipping;
                    $shippingStatus = 'processing';

                    if ($shipping && strtoupper($shipping->shipping_method) === 'LALAMOVE') {
                        $shippingStatus = 'in_transit';
                    }

                    $this->record->update([
                        'order_status' => 'processing',
                    ]);

                    $this->record->payment()->update([
                        'status' => 'paid',
                    ]);

                    if ($shipping) {
                        $shipping->update([
                            'shipping_status' => $shippingStatus,
                            'delivered_at' => now(),
                        ]);
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Warning: Shipping record missing for this order.')
                            ->warning()
                            ->send();
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Order Accepted')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reject')
               ->label('Reject Order')
               ->icon('heroicon-o-x-circle')
               ->color('danger')
               ->requiresConfirmation()
               ->modalHeading('Reject Order')
               ->modalDescription('Are you sure you want to reject this order? The stock will be restored.')
               ->modalSubmitActionLabel('Yes, Reject Order')
               ->visible(fn () => $this->record->order_status === 'pending')
               ->action(function () {
                   foreach ($this->record->orderItems as $item) {
                       if ($item->productVariant) {
                           $item->productVariant->increment('stock_quantity', $item->quantity);
                       } elseif ($item->product) {
                           $item->product->increment('stock_quantity', $item->quantity);
                       }
                   }

                   $this->record->update([
                       'stock_deducted' => false,
                       'order_status' => 'cancelled',
                   ]);

                   if ($this->record->payment) {
                       $this->record->payment->update(['status' => 'failed']);
                   }

                   \Filament\Notifications\Notification::make()
                       ->title('Order Rejected Successfully')
                       ->body("Order #{$this->record->orderID} has been rejected and products have been restocked.")
                       ->success()
                       ->send();
               }),

            Actions\Action::make('in_transit')
                ->label('In Transit')
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->shipping?->shipping_status === 'processing')
                ->action(function () {
                    $this->record->shipping->update([
                        'shipping_status' => 'in_transit',
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Order In Transit')
                        ->warning()
                        ->send();
                }),
                
            Actions\Action::make('deliver')
                ->label('Delivered')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->shipping?->shipping_status === 'in_transit')
                ->action(function () {
                    $shipping = $this->record->shipping;

                    if ($shipping) {
                        $shipping->update([
                            'shipping_status' => 'delivered',
                        ]);
                    } else {
                         \Filament\Notifications\Notification::make()
                            ->title('Error: Cannot mark as delivered, shipping record missing.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    $this->record->update([
                        'order_status' => 'completed',
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Order Delivered')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function infolist(Infolist $infolist): infolist
    {
        return $infolist
            ->schema([
                Section::make('Customer Details')
                    ->schema([
                        TextEntry::make('customer.full_name')
                            ->label('Customer Name')
                            ->icon('heroicon-o-user'),
                        TextEntry::make('customer.user.email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope'),
                        TextEntry::make('customer.user.phone')
                            ->label('Phone Number')
                            ->icon('heroicon-o-phone'),
                        TextEntry::make('formatted_shipping_address')
                            ->label('Address')
                            ->icon('heroicon-o-map-pin')
                            ->badge(false),
                    ])
                    ->columns(4),

                Group::make()
                    ->schema([
                        Section::make('Payment Details')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('payment.paymentMethod.method_name')
                                        ->label('Payment Method')
                                        ->icon('heroicon-o-arrow-right-circle')
                                        ->placeholder('— N/A —'),

                                    TextEntry::make('payment.reference_number')
                                        ->label('Reference No.')
                                        ->icon('heroicon-o-currency-dollar')
                                        ->placeholder('— N/A —'),

                                    TextEntry::make('payment.status')
                                        ->label('Payment Status')
                                        ->badge()
                                        ->color(fn (string $state): string => match (strtolower($state)) {
                                            'paid', 'complete' => 'success',
                                            'pending', 'processing' => 'warning',
                                            'failed', 'cancelled', 'refunded' => 'danger',
                                            default => 'gray',
                                        }),

                                    TextEntry::make('payment.amount')
                                        ->label('Total Amount to Pay')
                                        ->icon('heroicon-o-banknotes')
                                        ->money('PHP'),
                                ]),
                            ]),

                        Section::make('Shipping Details')
                            ->schema([
                                TextEntry::make('shipping.shipping_method')
                                    ->label('Shipping Method')
                                    ->icon('heroicon-o-truck')
                                    ->placeholder('— N/A —'),

                                TextEntry::make('shipping.shipping_status')
                                    ->label('Shipping Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match (strtolower($state)) {
                                        'delivered' => 'success',
                                        'in_transit', 'processing' => 'info',
                                        'pending' => 'warning',
                                        default => 'danger',
                                    })
                                    ->placeholder('— N/A —'),

                                TextEntry::make('shipping.delivered_at')
                                    ->label('Delivered Date')
                                    ->dateTime('M d, Y h:i A')
                                    ->icon('heroicon-o-archive-box')
                                    ->placeholder('— Not Delivered —'),

                            ])->columns(2),     
                    ])->columns(2),

                Group::make()
                    ->schema([
                        Section::make('Proof of Payment')
                            ->schema([
                                ImageEntry::make('payment.screenshot_path')
                                    ->label('Image upload')
                                    ->height(370) 
                                    ->width(450)
                                    ->defaultImageUrl(fn () => null)
                                    ->visible(fn ($record) => filled($record->payment?->screenshot_path)),
                            ])
                            ->columnSpan(2), 
                    ])
                    ->columns(2),

                    Section::make('Order Details')
                            ->schema([
                                TextEntry::make('order_date') 
                                    ->label('Date Placed')
                                    ->dateTime('M d, Y h:i A')
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('order_status')
                                    ->label('Order Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match (strtolower($state)) {
                                        'pending', 'processing' => 'warning',
                                        'completed' => 'success',
                                        'failed', 'cancelled', 'refunded' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('orderitems.discount.name')
                                    ->label('Discount Applied')
                                    ->placeholder('None'),

                                TextEntry::make('overall_discount')
                                    ->label('Overall Discount')
                                    ->money('PHP')
                                    ->getStateUsing(fn($record) => $record->orderItems->sum('discount_amount') ?: 0),

                                TextEntry::make('shipping.shipping_fee')
                                    ->label('Shipping Fee')
                                    ->money('PHP')
                                    ->getStateUsing(fn($record) => $record->shipping?->shipping_fee ?? 0),

                                TextEntry::make('payment.amount')
                                    ->label('Final Total')
                                    ->size(TextEntrySize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->money('PHP')
                            ])->columns(6),

                    Section::make('Products Ordered')
                        ->schema([
                            RepeatableEntry::make('orderItems')
                                ->label('Items')
                                ->contained(false)
                                ->schema([
                                    TextEntry::make('product.name')
                                        ->label('Product'),
                                    TextEntry::make('productvariant.colorway')
                                        ->label('Colorway'),
                                    TextEntry::make('size')
                                        ->label('Size')
                                        ->getStateUsing(fn($record) => $record->size ?? '—'),
                                    TextEntry::make('quantity')
                                        ->label('Qty'),
                                    TextEntry::make('unit_price')
                                        ->label('Unit Price')
                                        ->money('PHP'),
                                    TextEntry::make('discount_amount')
                                        ->label('Discount Price')
                                        ->money('PHP')
                                        ->hidden(fn($record) => $record->discount_amount == 0),
                                    TextEntry::make('line_total')
                                        ->label('Subtotal')
                                        ->getStateUsing(fn($record) => 
                                            ($record->quantity * $record->unit_price) - $record->discount_amount
                                        )
                                        ->money('PHP'),
                                ])
                                ->columns(7)
                                ->contained(false),
                        ]),
                        

        ]); 
    }
}
