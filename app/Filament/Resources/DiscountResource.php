<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;

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

                        Select::make('products')
                            ->label('Applies To Product')
                            ->relationship('products', 'name', null, false, 'productID')
                            ->searchable()
                            ->multiple()
                            ->preload()
                            ->required()
                            ->options(function (Get $get, ?Discount $record): array {
                                $products = Product::whereDoesntHave('discounts', function (Builder $query) use ($record) {
                                    $query->where('is_active', true);
                                    if ($record) {
                                        $query->where('discounts.discountID', '!=', $record->discountID);
                                    }
                                })
                                ->pluck('name', 'productID')
                                ->toArray();

                                return $products;
                            }),

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
                            ->helperText('Enter a value (e.g., 10 for ₱10 discount or 10% discount)'),

                        DateTimePicker::make('start_date')
                            ->default(now())
                            ->required(),

                        DateTimePicker::make('end_date')
                            ->required()
                            ->rule('after:start_date')
                            ->helperText('End date must be later than start date'),

                        Toggle::make('is_active')
                            ->label('Is Active?')
                            ->required()
                            ->default(true),

                    ])->columns(2),
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
                        if ($record->discount_type == 'Fixed') {
                            return '₱' . $record->discount_value;
                        } else {
                            return $record->discount_value . '%';
                        }
                    })
                    ->sortable(),

                TextColumn::make('start_date')
                    ->dateTime('M d, Y H:i')
                    ->label('Starts')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->dateTime('M d, Y H:i')
                    ->label('Ends')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->is_active &&
                            (! $record->start_date || now()->gte($record->start_date)) &&
                            (! $record->end_date || now()->lte($record->end_date));
                    }),

                TextColumn::make('products.name')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Created At')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Updated At')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Archived At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
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
        return [];
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
