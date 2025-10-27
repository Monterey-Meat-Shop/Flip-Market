<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnRequestResource\Pages;
use App\Models\ReturnRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReturnRequestResource extends Resource
{
    protected static ?string $model = ReturnRequest::class;
    
    //protected static ?string $recordTitleAttribute = 'returnID';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Return Requests';
    
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('return_status', 'pending')->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
                ->with(['order', 'customer', 'customer.user']);
    }

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();

        if ($user && $user->hasRole(['admin'])) {
           return 'Sales';
        }
        return null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Return Information')
                    ->schema([
                        // Select::make('orderID')
                        //     ->label('Order')
                        //     ->relationship('order', 'orderID'),
                        
                        Placeholder::make('customer')
                            ->label('Customer')
                            ->content(function ($record) {
                                if (!$record || !$record->customer) {
                                    return '-';
                                }

                                // combine first and last name
                                $first = $record->customer->first_name ?? '';
                                $last = $record->customer->last_name ?? '';

                                return trim("{$first} {$last}") ?: '-';
                            }),
                       
                        Placeholder::make('product_details')
                            ->label('Returned Items')
                            ->content(function ($record) {
                                if (!$record || empty($record->returned_items) || !is_array($record->returned_items)) {
                                    return '-';
                                }
                                
                                // Load all related order items (with product + variant)
                                $orderItems = $record->order
                                    ? $record->order->orderItems()->with(['product', 'productVariant'])->get()
                                    : collect();

                                // Build the list of returned products
                                $details = collect($record->returned_items)
                                    ->map(function ($item) use ($orderItems) {
                                        $orderItemId = $item['order_itemID'] ?? null;
                                        if (!$orderItemId) return null;

                                        $orderItem = $orderItems->firstWhere('order_itemID', $orderItemId);
                                        if (!$orderItem) return null;

                                        $productName = $orderItem->product->name ?? 'Unknown Product';
                                        $color = $orderItem->colorway ?? 'N/A';
                                        $size = $orderItem->productVariant->size ?? 'N/A';
                                        $qty = $item['quantity'] ?? 1;

                                        return "• {$productName} ({$color} | Size: {$size}) × {$qty}";
                                    })
                                    ->filter()
                                    ->implode('<br>');

                                // Return final formatted string
                                return $details ?: '-';
                            }),

                        Placeholder::make('return_reason')
                            ->label('Return Reason')
                            ->content(function ($record) {
                                if (!$record) {
                                    return '-';
                                }

                                $reasons = [
                                    'not_delivered' => 'Product Not Delivered',
                                    'defective' => 'Defective or Damaged',
                                    'changed_mind' => 'Changed Mind/Not as Expected',
                                    'incorrect' => 'Incorrect Product',
                                    'other' => 'Other Reason',
                                ];

                                return $reasons[$record->return_reason] ?? $record->return_reason ?? '-';
                            }),

                        FileUpload::make('product_image')
                            ->label('Product Image')
                            ->image()
                            ->disk('public')
                            ->directory('returns')
                            ->disabled()
                            ->downloadable()
                            ->openable(),
                    ])
                    ->columns(3),
                
                Section::make('Return Status & Processing')
                    ->schema([
                        Select::make('return_status')
                            ->label('Return Status')
                            ->options(function ($record) {
                                $options = [
                                    'pending' => 'Pending Review',
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                    'completed' => 'Completed',
                                ];

                                if ($record) {
                                    // If already approved → remove Pending Review from options
                                    if ($record->return_status === 'approved') {
                                        unset($options['pending']);
                                    }

                                    // If already completed or rejected → disable dropdown entirely
                                    if (in_array($record->return_status, ['completed', 'rejected'])) {
                                        return $options; // still needed to render but will be disabled below
                                    }
                                }

                                return $options;
                            })
                            ->required()
                            ->live()
                            ->disabled(function ($record) {
                                // Disable select if record is completed or rejected
                                return in_array($record?->return_status, ['completed', 'rejected']);
                            })
                            ->afterStateUpdated(function ($state, $record) {
                                if (!$record) {
                                    return;
                                }

                                if ($state === 'approved') {
                                    $record->approved_at = now();
                                } elseif ($state === 'rejected') {
                                    $record->rejected_at = now();
                                } elseif ($state === 'completed') {
                                    $record->completed_at = now();
                                }

                                $record->save();
                            }),
                        
                        DateTimePicker::make('approved_at')
                            ->label('Approved At')
                            ->disabled()
                            ->visible(fn ($get) => $get('return_status') === 'approved'),
                        
                        DateTimePicker::make('rejected_at')
                            ->label('Rejected At')
                            ->disabled()
                            ->visible(fn ($get) => $get('return_status') === 'rejected'),
                        
                        DateTimePicker::make('completed_at')
                            ->label('Completed At')
                            ->disabled()
                            ->visible(fn ($get) => $get('return_status') === 'completed'),

                        Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(4)
                            ->placeholder('Add notes about this return request...')
                            ->helperText('These notes are internal and will not be visible to customers.'),

                    ])->columns(2),
                    
                Section::make('Timestamps')
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn ($record): string => $record?->created_at?->diffForHumans() ?? '-'),
                        
                        Placeholder::make('updated_at')
                            ->label('Last Updated')
                            ->content(fn ($record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['order', 'customer.user']))
            ->columns([
                // TextColumn::make('returnID')
                //     ->label('Return ID'),
                
                // TextColumn::make('order.orderID')
                //     ->label('Order ID'),
                
                TextColumn::make('customer.first_name')
                    ->label('Customer')
                    ->formatStateUsing(fn ($record) => $record->customer->first_name . ' ' . $record->customer->last_name)
                    ->sortable()
                    ->searchable(['first_name', 'last_name']),
                
                TextColumn::make('return_reason')
                    ->label('Reason')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'not_delivered' => 'Not Delivered',
                        'defective' => 'Defective/Damaged',
                        'changed_mind' => 'Changed Mind',
                        'incorrect' => 'Incorrect Product',
                        'other' => 'Other',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'not_delivered' => 'danger',
                        'defective' => 'warning',
                        'changed_mind' => 'info',
                        'incorrect' => 'warning',
                        'other' => 'gray',
                        default => 'gray',
                    }),
                
                TextColumn::make('return_status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'rejected' => 'danger',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                
                // ImageColumn::make('product_image')
                //     ->label('Image')
                //     ->disk('public')
                //     ->square()
                //     ->size(50),
                
                TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('order.final_amount')
                    ->label('Order Amount')
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Last Updated')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->label('Archived At')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('return_status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ])
                    ->multiple(),
                
                SelectFilter::make('return_reason')
                    ->label('Reason')
                    ->options([
                        'not_delivered' => 'Not Delivered',
                        'defective' => 'Defective/Damaged',
                        'changed_mind' => 'Changed Mind',
                        'incorrect' => 'Incorrect Product',
                        'other' => 'Other',
                    ])
                    ->multiple(),
                
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('From'),
                        DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Archive')
                    ->visible(fn ($record) => in_array($record->return_status, ['completed', 'rejected']))
                    ->modalHeading('Archive Return Request')
                    ->modalDescription('Are you sure you want to archive this return request? You can restore it later if needed.')
                    ->modalSubmitActionLabel('Archive') 
                    ->modalCancelActionLabel('Cancel') 
                    ->color('danger')
                    ->icon('heroicon-o-archive-box')
                    ->visible(fn ($record) => in_array($record->return_status, ['completed', 'rejected'])),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->return_status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'return_status' => 'approved',
                            'approved_at' => now(),
                        ]);
                        
                        $record->order->update([
                            'order_status' => 'return_requested',
                        ]);
                        
                        Notification::make()
                            ->title('Return Approved')
                            ->success()
                            ->body('Return request has been approved.')
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->return_status === 'pending')
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('Explain why this return is being rejected...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'return_status' => 'rejected',
                            'rejected_at' => now(),
                            'admin_notes' => $data['admin_notes'],
                        ]);
                        
                        Notification::make()
                            ->title('Return Rejected')
                            ->danger()
                            ->body('Return request has been rejected.')
                            ->send();
                    }),
                
                Tables\Actions\Action::make('complete')
                    ->label('Mark Complete')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->return_status === 'approved')
                    ->action(function ($record) {
                        $record->update([
                            'return_status' => 'completed',
                            'completed_at' => now(),
                        ]);
                        
                        $record->order->update([
                            'order_status' => 'returned',
                        ]);
                        
                        Notification::make()
                            ->title('Return Completed')
                            ->success()
                            ->body('Return has been marked as completed.')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->return_status === 'pending') {
                                    $record->update([
                                        'return_status' => 'approved',
                                        'approved_at' => now(),
                                    ]);
                                    
                                    $record->order->update([
                                        'order_status' => 'return_requested',
                                    ]);
                                }
                            }
                            
                            Notification::make()
                                ->title('Returns Approved')
                                ->success()
                                ->body('Selected return requests have been approved.')
                                ->send();
                        }),
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
            'index' => Pages\ListReturnRequests::route('/'),
            'create' => Pages\CreateReturnRequest::route('/create'),
            'view' => Pages\ViewReturnRequest::route('/{record}'),
            'edit' => Pages\EditReturnRequest::route('/{record}/edit'),
        ];
    }
}