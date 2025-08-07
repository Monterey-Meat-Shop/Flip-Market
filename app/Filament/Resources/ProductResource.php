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
use Filament\Tables\Columns\TagsColumn;
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
    protected static ?string $navigationGroup = 'Products';

    // This method controls who can see the 'Products' navigation item
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['admin', 'manager']);
    }

    // These methods control permissions within the resource
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'manager']);
    }
    
    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function canForceDelete(Model $record): bool
    {
        return auth()->user()->hasRole('admin');
    }
    
    public static function canRestore(Model $record): bool
    {
        return auth()->user()->hasRole('admin');
    }
    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Group::make()->schema([
                Section::make('Product Information')->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(225)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function(string $operation, $state, Set $set) {
                            if ($operation !== 'create') {
                                return;
                            }
                            $set('slug', Str::slug($state));
                        }),

                    TextInput::make('slug')
                        ->required()
                        ->maxLength(225)
                        ->disabled()
                        ->dehydrated()
                        ->unique(Product::class, 'slug', ignoreRecord:true),

                    RichEditor::make('description')
                        ->fileAttachmentsDirectory('products/descriptions')
                        ->columnSpanFull()
                        ->maxLength(1000),
                    
                    TextInput::make('stock_quantity')
                        ->numeric()
                        ->required()
                        ->reactive()
                        // ->afterStateUpdated(function($state, callable $set) {
                            
                        // })
                        ,

                    TextInput::make('colorway')
                        ->required()
                        ->maxLength(225),

                    CheckboxList::make('size')
                        ->label('Size')
                        ->options([
                            '36' => '36', '37' => '37', '38' => '38', '39' => '39', '40' => '40', '41' => '41', '42' => '42', '43' => '43', '44' => '44', '45' => '45', '46' => '46',
                        ])
                        ->columns(6)
                        ->columnSpanFull()
                        ->required()
                        //->dehydrated(true),
                        
                ])->columns(2),

                Section::make('Images')->schema([
                    FileUpload::make('image_url')
                        ->multiple()
                        ->directory('products')
                        ->visibility('public')
                        ->maxFiles(5)
                        ->reorderable(),
                ])
            ])->columnSpan(2),
            
            Group::make()->schema([ 
                Section::make('Price')->schema([
                    TextInput::make('price')
                        ->numeric()
                        ->rules(['required', 'numeric', 'min:1'])
                        ->required()
                        ->prefix('PHP'),
                ]),

                Section::make('Association')->schema([
                    Select::make('categoryID')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('category', 'name'),

                    Select::make('brandID')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('brand', 'name'),
                ]),

                Section::make('Status')->schema([
                    Select::make('status')
                        ->required()
                        ->options([
                            'in_stock' => 'In Stock',
                            'pre_order' => 'Pre-order',
                            'out_of_stock' => 'Out of Stock',
                        ])
                        ->default('in_stock')
                        ->afterStateUpdated(function (string $state, callable $set) {
                            // A pre-order product is considered active regardless of stock.
                            if ($state === 'pre_order') {
                                $set('is_active', true);
                            } else {
                                // For 'in_stock' or 'out_of_stock', depend on stock quantity
                                $set('is_active', (int) $set('stock_quantity') > 0);
                            }
                        }),
                        
                    Toggle::make('is_active')
                        ->required()
                        ->default(true)
                        ->helperText('This field is automatically managed, but you can override it.'),
                ]),
            ])->columnSpan(1)
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                ImageColumn::make('image_url')
                    ->label('Image')
                    ->getStateUsing(fn ($record) => $record->image_url[0] ?? null), 

                TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_stock' => 'success',
                        'pre_order' => 'info',
                        'out_of_stock' => 'danger',
                    }),
                
                TextColumn::make('stock_quantity')
                    ->label('Stock Quantity')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->money('PHP')
                    ->sortable(),

                TagsColumn::make('size')
                    ->label('Sizes')
                    ->searchable()
                    ->sortable(),
            
                IconColumn::make('is_active')
                    ->getStateUsing(fn (Product $record): bool => $record->trashed() ? false : $record->is_active)
                    ->boolean(),

                TextColumn::make('deleted_at')
                    ->label('Archived Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'in_stock' => 'In Stock',
                        'pre_order' => 'Pre-order',
                        'out_of_stock' => 'Out of Stock',
                    ])
                    ->label('Status'),

                SelectFilter::make('category')
                    ->relationship('category', 'name'),

                SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
                    
                // TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
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
        // Return the base query including soft-deleted records
        return parent::getEloquentQuery()->withTrashed();
    }

    public static function resolveRecordRouteBinding(int | string $key): ?Model
    {
        // The query here is important for finding records that have been soft-deleted.
        return static::getModel()::where('ProductID', $key)->withTrashed()->first();
    }
}