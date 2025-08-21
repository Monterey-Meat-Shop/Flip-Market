<?php

namespace App\Filament\Resources;

use App\Models\Report;
use App\Filament\Resources\ReportResource\Pages;
use App\Filament\Resources\ReportResource\Widgets\ReportStats;
use App\Filament\Resources\ReportResource\Widgets\RecentReportsTable;
use App\Filament\Resources\ReportResource\Widgets\ReportsLineChart;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Reports & Analytics';
    protected static ?string $navigationLabel = 'Reports';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->placeholder('Enter report title'),

            Forms\Components\Textarea::make('description')
                ->rows(4)
                ->placeholder('Write a short description'),

            Forms\Components\Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                    'archived' => 'Archived',
                ])
                ->default('draft')
                ->required()
                ->label('Report Status'),

            Forms\Components\DatePicker::make('report_date')
                ->label('Report Date')
                ->default(now()),

            Forms\Components\FileUpload::make('attachment')
                ->label('Upload File')
                ->directory('reports/attachments')
                ->downloadable(),

            Forms\Components\Toggle::make('is_visible')
                ->label('Visible in Dashboard')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->label('ID'),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'draft',
                        'success' => 'published',
                        'warning' => 'archived',
                    ])->sortable(),
                Tables\Columns\TextColumn::make('report_date')->date()->label('Date'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M d, Y H:i')->label('Created'),
                Tables\Columns\IconColumn::make('is_visible')->boolean()->label('Visible'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ReportStats::class,
            RecentReportsTable::class,
            ReportsLineChart::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Report::count();
    }
}