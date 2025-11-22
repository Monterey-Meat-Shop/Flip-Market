<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages\ListReports;
use App\Filament\Resources\ReportResource\Widgets\ReportStats;
use App\Filament\Resources\ReportResource\Widgets\OrdersStats;
use App\Filament\Resources\ReportResource\Widgets\PaymentsStats;
use App\Filament\Resources\ReportResource\Widgets\OrdersRealtimeStats;
use App\Filament\Resources\ReportResource\Widgets\RecentReportsTable;
use App\Models\Report;
use App\Models\ReportMetric;
use App\Models\OrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables; 
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Sales Reports';

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();
    
        if ($user && $user->hasRole('admin')) {
           return 'Reports';
        }
        return null;
    }

    // public static function form(Form $form): Form
    // {
    //     return $form->schema([
    //         Forms\Components\TextInput::make('title')->required(),
    //         Forms\Components\Select::make('status')
    //             ->options([
    //                 'published' => 'Published',
    //                 'draft'     => 'Draft',
    //             ])
    //             ->required(),
    //     ]);
    // }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['admin', 'manager']);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->sortable(),
            
            Tables\Columns\TextColumn::make('title')
                ->label('Report Title')
                ->searchable()
                ->wrap()
                ->weight('bold'),

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
                ->label('Total Orders')
                ->getStateUsing(function () {
                    if (!class_exists(ReportMetric::class)) {
                        return 0;
                    }
                    $m = ReportMetric::orderByDesc('metric_date')->first();
                    return $m ? $m->orders_count : 0;
                })
                ->badge()
                ->color('info')
                ->sortable(),

            Tables\Columns\TextColumn::make('sales_total')
                ->label('Total Sales')
                ->getStateUsing(function () {
                    if (!class_exists(ReportMetric::class)) {
                        return '₱0.00';
                    }
                    $m = ReportMetric::orderByDesc('metric_date')->first();
                    return $m ? '₱' . number_format($m->orders_total, 2) : '₱0.00';
                })
                ->weight('bold')
                ->color('success')
                ->sortable(),

            Tables\Columns\TextColumn::make('unique_customers')
                ->label('Customers')
                ->getStateUsing(function () {
                    if (!class_exists(ReportMetric::class)) {
                        return 0;
                    }
                    $m = ReportMetric::orderByDesc('metric_date')->first();
                    return $m ? ($m->unique_customers ?? 0) : 0;
                })
                ->badge()
                ->color('warning')
                ->tooltip('Unique customers in this period'),

            Tables\Columns\TextColumn::make('products_sold')
                ->label('Products Sold')
                ->getStateUsing(function () {
                    if (!class_exists(ReportMetric::class)) {
                        return 0;
                    }
                    $m = ReportMetric::orderByDesc('metric_date')->first();
                    return $m ? ($m->total_products_sold ?? 0) : 0;
                })
                ->badge()
                ->color('success')
                ->tooltip('Total quantity of products sold'),

            Tables\Columns\TextColumn::make('avg_order_value')
                ->label('Avg Order')
                ->getStateUsing(function () {
                    if (!class_exists(ReportMetric::class)) {
                        return '₱0.00';
                    }
                    $m = ReportMetric::orderByDesc('metric_date')->first();
                    
                    if (!$m || $m->orders_count == 0) {
                        return '₱0.00';
                    }
                    
                    $avg = $m->orders_total / $m->orders_count;
                    return '₱' . number_format($avg, 2);
                })
                ->sortable(),

            Tables\Columns\IconColumn::make('has_fast_movers')
                ->label('Fast Movers')
                ->getStateUsing(function () {
                    // Check if there are any fast-moving products (10+ orders in last 30 days)
                    if (!class_exists(OrderItem::class)) {
                        return false;
                    }
                    
                    $count = OrderItem::whereHas('order', function($q) {
                        $q->where('status', '!=', 'cancelled')
                          ->where('created_at', '>=', now()->subDays(30));
                    })
                    ->selectRaw('product_id, COUNT(DISTINCT order_id) as order_count')
                    ->groupBy('product_id')
                    ->having('order_count', '>=', 10)
                    ->count();
                    
                    return $count > 0;
                })
                ->boolean()
                ->trueIcon('heroicon-o-fire')
                ->falseIcon('heroicon-o-minus')
                ->trueColor('danger')
                ->falseColor('gray')
                ->tooltip('Products with high demand'),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Created At')
                ->dateTime('M j, Y g:i A')
                ->sortable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('period')
                ->label('Period')
                ->getStateUsing(function ($record) {
                    // Extract period from report metadata or title
                    // This is a placeholder - adjust based on your actual data structure
                    return $record->period ?? 'Weekly';
                })
                ->badge()
                ->color('gray')
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'published' => 'Published',
                    'draft' => 'Draft',
                ]),
            Tables\Filters\Filter::make('created_at')
                ->form([
                    Forms\Components\DatePicker::make('created_from')
                        ->label('Created from'),
                    Forms\Components\DatePicker::make('created_until')
                        ->label('Created until'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['created_from'], fn ($q) => $q->whereDate('created_at', '>=', $data['created_from']))
                        ->when($data['created_until'], fn ($q) => $q->whereDate('created_at', '<=', $data['created_until']));
                }),
        ])
        ->actions([
            Tables\Actions\ViewAction::make()
                ->label('View Details'),
            Tables\Actions\Action::make('download')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (Report $record): string => route('reports.download', $record))
                ->openUrlInNewTab(),
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
            Tables\Actions\BulkAction::make('export')
                ->label('Export Selected')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function ($records) {
                    // Implement bulk export logic
                })
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion(),
        ])
        ->defaultSort('created_at', 'desc')
        ->poll('30s'); // Auto-refresh every 30 seconds
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
            ReportStats::class,
            OrdersStats::class,
            PaymentsStats::class,
            OrdersRealtimeStats::class,
            RecentReportsTable::class,
        ];
    }
}