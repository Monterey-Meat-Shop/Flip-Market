<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Filament\Resources\DiscountResource\RelationManagers;
use App\Models\Discount;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Payments';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Discount Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(225),

                    Select::make('discount_type')
                        ->label('Discount Type')
                        ->options([
                            'Fixed' => 'Fixed Amount',
                            'Percentage' => 'Percentage',
                        ]),

                    TextInput::make('discount_value')
                        ->required()
                        ->numeric()
                        ->rules(['min:0'])
                        ->helperText('Enter a value (e.g., 10 for a ₱10 discount or a 10% discount)'),

                    Toggle::make('is_active') // This is the correct form component
                        ->label('Is Active?')
                        ->required()
                        ->default(true),

                    Select::make('products')
                        ->label('Applies To Product')
                        ->relationship('products', 'name')
                        ->searchable()
                        ->multiple()
                        ->preload()
                        ->required(),

                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('discount_type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('discount_value')
                    ->label('Discounts')
                    ->formatStateUsing(function ($state, $record): string {
                        if($record->discount_type == 'Fixed') {
                            return '₱'.$record->discount_value;
                        } else {
                            return $record->discount_value.'%';
                        }
                        
                    })
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('products.name')
                    //->searchable()
                    ->sortable(), 
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
