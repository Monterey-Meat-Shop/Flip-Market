<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Resources\Resource;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Group::make()->schema([
                Section::make('Product Information')->schema([
                    TextInput::make('Name')
                        ->required()
                        ->maxLength(225)
                        ->live(onBlur: true)
                        // This is for the slug
                        ->afterStateUpdated(function(string $operation, $state, Set $set) {
                            if ($operation !== 'create') {
                                return;
                            }
                            $set('Slug', Str::slug($state));
                        }),

                    TextInput::make('Slug')
                        ->required()
                        ->maxLength(225)
                        ->disabled()
                        ->dehydrated()
                        ->unique(Product::class, 'slug', ignoreRecord:true),

                    RichEditor::make('Description')
                        ->fileAttachmentsDirectory('products/descriptions')
                        ->columnSpanFull()
                        ->maxLength(1000),
                    
                    TextInput::make('Stock_Quantity')
                        ->numeric()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function($state, callable $set) {
                            //check if the state is less than or equal to 0
                            if((int)$state <=0){
                                $set('is_active', (int) $state > 0);
                            } 
                        }),

                    TextInput::make('Colorway')
                        ->required()
                        ->maxLength(225),

                    CheckboxList::make('Size')
                        ->label('Size')
                        ->options([
                            '36' => '36',
                            '37' => '37',
                            '38' => '38',
                            '39' => '39',
                            '40' => '40',
                            '41' => '41',
                            '42' => '42',
                            '43' => '43',
                            '44' => '44',
                            '45' => '45',
                            '46' => '46',
                    ])
                    ->columns(6)//5
                    ->columnSpanFull()
                    ->required()

                ])->columns(2),

                Section::make('Images')->schema([
                    FileUpload::make('image_url')
                        ->multiple()
                        ->directory('products')
                        ->visibility('public')
                        ->maxFiles(5)
                        ->reorderable()
                ])
            ])->columnSpan(2),
            
            Group::make()->schema([ 
                Section::make('Price')->schema([
                    TextInput::make('Price')
                        ->numeric()
                        ->required()
                        ->prefix('PHP')
                    
                ]),

                Section::make('Association')->schema([
                    Select::make('CategoryID')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('category', 'name'),

                        Select::make('BrandID')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('brand', 'name')
                ]),

                Section::make('Status')->schema([
                    Toggle::make('in_stock')
                        ->required()
                        ->default(true),

                    Toggle::make('is_active')
                        ->required()
                        ->default(true),

                    /*
                    Toggle::make('in_feature')
                        ->required()
                        ,

                    Toggle::make('on_sale')
                        ->required()
                    */    
                ])

            ])->columnSpan(1)

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('Name')
                    ->searchable()
                    ->sortable(),

                ImageColumn::make('image_url')
                    ->label('Image')
                    //This is for storing multiple images
                    ->getStateUsing(fn ($record) => $record->image_url[0] ?? null),   

                TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('Stock_Quantity')
                    ->label('Stock Info')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('Price')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('Size')
                    ->sortable(),
            
                // Displays a boolean icon for the 'is_active' column, showing 'false' if the product is soft-deleted and otherwise using the 'is_active' value.
                IconColumn::make('is_active')
                    ->getStateUsing(fn (Product $record): bool => $record->trashed() ? false : $record->is_active)
                    ->boolean(),

                // Added a new column for the deleted_at timestamp
                TextColumn::make('deleted_at')
                    ->label('Archived Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Column for the create_at and updated_at timestamps
                TextColumn::make('created_at')
                    ->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
            ])
            ->filters([
                // This filter, filters by the 'category_id' column in the 'products' table.
                SelectFilter::make('category')
                    ->relationship('category', 'name'),

                SelectFilter::make('brand')
                    ->relationship('brand', 'name'),

                // This filter explicitly modifies the query to include soft-deleted records.
                TrashedFilter::make()
                    ->modifyQueryUsing(fn (Builder $query) => $query->withTrashed())
                    ->indicator('Archived', fn (Builder $query) => $query->whereNotNull('deleted_at')),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    // The standard DeleteAction performs a soft delete when SoftDeletes is used.
                    DeleteAction::make()
                        ->visible(fn (Product $record): bool => ! $record->trashed()),
                    // This action restores a soft-deleted record.
                    RestoreAction::make()
                        ->visible(fn (Product $record): bool => $record->trashed()),
                    // This action permanently deletes a soft-deleted record.
                    ForceDeleteAction::make()
                        ->visible(fn (Product $record): bool => $record->trashed()),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // This is a custom query that excludes soft-deleted records.
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function resolveRecordRouteBinding(int | string $key): ?Model
    {
        //for archived records
        return static::getModel()::where('ProductID', $key)->withTrashed()->first();
    }
}
