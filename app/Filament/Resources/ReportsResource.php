<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportsResource\Pages;
use App\Models\Reports;
use Filament\Resources\Resource;
use Filament\Forms\Form;                 // ✅ correct Form import
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables;                     // for Tables\Actions\*

class ReportsResource extends Resource
{
    protected static ?string $model = Reports::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // form fields...
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([10, 25, 50, 100, 'all'])   // ✅ options
            ->defaultPaginationPageOption(25)       // ✅ default per page
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('customer_name')->label('Customer Name')->searchable()->sortable(),
                TextColumn::make('total_amount')->label('Total Amount')->sortable(),
                TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('order_status')->label('Order Status')->options([
                    'pending' => 'Pending',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ]),
                SelectFilter::make('payment_status')->label('Payment Status')->options([
                    'paid' => 'Paid',
                    'failed' => 'Failed',
                    'pending' => 'Pending',
                ]),
                SelectFilter::make('payment_method')->label('Payment Method')->options([
                    'credit_card' => 'Credit Card',
                    'paypal' => 'PayPal',
                    'bank' => 'Bank Transfer',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReports::route('/create'),
            'edit' => Pages\EditReports::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
{
    return [
        ReportStats::class,
    ];
}

}
