<?php

namespace App\Filament\Resources;

use App\Models\Report;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ReportResource\Pages;
use App\Filament\Resources\ReportResource\Pages\ListReports;
use App\Filament\Resources\ReportResource\Pages\CreateReport;
use App\Filament\Resources\ReportResource\Pages\EditReport;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    //protected static ?string $navigationGroup = 'Reports';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\Textarea::make('description'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

   public static function getPages(): array
{
    return [
        'index'  => Pages\ListReports::route('/'),         
        'create' => Pages\CreateReport::route('/create'),  
        'edit'   => Pages\EditReport::route('/{record}/edit'), 
    ];
}


public static function shouldRegisterNavigation(): bool
{
    return true;
}

public static function getNavigationLabel(): string
{
    return 'Reports';
}

public static function getNavigationIcon(): string
{
    return 'heroicon-o-document-text';
}

}