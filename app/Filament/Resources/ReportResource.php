<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages\ListReports;
use App\Filament\Resources\ReportResource\Widgets\OrdersRealtimeStats;
use App\Filament\Resources\ReportResource\Widgets\RecentReportsTable;
use App\Models\Report;
use App\Models\ReportMetric;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables; 
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Reports';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\Select::make('status')
                ->options([
                    'published' => 'Published',
                    'draft'     => 'Draft',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('title')->label('Title')->searchable()->wrap(),

            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'published' => 'Published',
                    'draft'     => 'Draft',
                    default     => ucfirst($state),
                })
                ->color(fn (string $state): string => match ($state) {
                    'published' => 'success',
                    'draft'     => 'secondary',
                    default     => 'gray',
                })
                ->sortable(),

            Tables\Columns\TextColumn::make('orders_count')
                ->label('Order Count')
                ->getStateUsing(function () {
                    if (! class_exists(ReportMetric::class)) {
                        return 0;
                    }
                    $m = ReportMetric::orderByDesc('metric_date')->first();
                    return $m ? $m->orders_count : 0;
                })
                ->sortable(),

            Tables\Columns\TextColumn::make('sales_total')
                ->label('Sales Total')
                ->getStateUsing(function () {
                    if (! class_exists(ReportMetric::class)) {
                        return '₱0.00';
                    }
                    $m = ReportMetric::orderByDesc('metric_date')->first();
                    return $m ? '₱' . number_format($m->orders_total, 2) : '₱0.00';
                })
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Created At')
                ->dateTime()
                ->sortable(),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReports::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrdersRealtimeStats::class,
            RecentReportsTable::class,
        ];
    }
}
