<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnItemResource\Pages;
use App\Models\ReturnItem;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;


class ReturnItemResource extends Resource
{
    protected static ?string $model = ReturnItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Return Products';
    protected static ?string $pluralLabel = 'Defective Products';

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();
    
        if ($user && $user->hasRole('admin')) {
           return 'Products';
        }
        return null;
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole('');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('returnID')
                    ->relationship('returnRequest', 'returnID')
                    ->label('Return Request')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')->sortable(),
                TextColumn::make('customer.first_name')
                    ->label('Customer')
                    ->formatStateUsing(fn ($record) => $record->customer->first_name . ' ' . $record->customer->last_name)
                    ->sortable()
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('productVariant.product.name')->label('Product Name')->sortable(),
                TextColumn::make('productVariant.colorway')->label('Colorways')->sortable(),
                TextColumn::make('productVariant.size')->label('Size')->sortable(),
                TextColumn::make('quantity'),
                // Tables\Columns\TextColumn::make('notes')->limit(30),
                Tables\Columns\TextColumn::make('created_at')->label('Return Date')->dateTime()->sortable(),
            ])
            ->filters([])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                // Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnItems::route('/'),
            // 'create' => Pages\CreateReturnItem::route('/create'),
            // 'edit' => Pages\EditReturnItem::route('/{record}/edit'),
        ];
    }
}
