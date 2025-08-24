<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['admin', 'manager']);
    }

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
    
    public static function getRouteKeyName(): ?string
    {
        return 'ProductID';
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
                    
                    Repeater::make('size_stocks')
                        ->label('Sizes & Stock')
                        ->relationship('variants')
                        ->schema([
                            TextInput::make('size')
                                ->numeric()
                                ->required()
                                ->maxLength(225),
                            TextInput::make('stock_quantity')
                                ->numeric()
                                ->required()
                                ->default(0),
                        ])
                        ->defaultItems(1)
                        ->columns(2)
                        ->columnSpanFull()
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, Get $get, ?array $state) {
                            $totalStock = collect($state)->sum('stock_quantity');
                            $currentStatus = $get('status');
                            
                            if ($currentStatus !== 'pre_order') {
                                if ($totalStock === 0) {
                                    $set('status', 'out_of_stock');
                                } elseif ($totalStock <= 4) {
                                    $set('status', 'low_stock');
                                } else {
                                    $set('status', 'in_stock');
                                }
                            }
                            
                            $currentStatus = $get('status');
                            $set('is_active', $currentStatus === 'pre_order' || $totalStock > 0);
                        }),
                        
                    TextInput::make('colorway')
                        ->required()
                        ->maxLength(225),
                        
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
                        ->relationship(
                            'category',
                            'name',
                            fn (Builder $query) => $query->where('is_active', true)
                        ),

                    Select::make('brandID')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship(
                            'brand',
                            'name',
                            fn (Builder $query) => $query->where('is_active', true)
                        ),
                ]),

                Section::make('Status')->schema([
                    Select::make('status')
                        ->required()
                        ->options([
                            'in_stock' => 'In Stock',
                            'low_stock' => 'Low Stock',
                            'pre_order' => 'Pre-order',
                            'out_of_stock' => 'Out of Stock',
                        ])
                        ->default('in_stock')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get, string $state) {
                            if ($state === 'pre_order') {
                                $set('is_active', true);
                            }
                            if ($state === 'out_of_stock') {
                                $set('is_active', false);
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

                // Updated to use the relationship directly
                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable(),

                // Updated to use the relationship directly
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                
                // New column to show discounts
                // TagsColumn::make('discounts.name')
                //     ->label('Discounts'),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_stock' => 'success',
                        'low_stock' => 'warning',
                        'pre_order' => 'info',
                        'out_of_stock' => 'danger',
                    }),
                
                TextColumn::make('size_stocks')
                    ->label('Sizes & Stock')
                    ->getStateUsing(function ($record) {
                        $output = '';
                        if ($record->variants && $record->variants->isNotEmpty()) {
                            $sizes = $record->variants->map(function ($variant) {
                                return "Size {$variant->size}: {$variant->stock_quantity}";
                            })->implode(', ');
                            $output = $sizes;
                        }
                        return $output;
                    }),

                TextColumn::make('price')
                    ->money('PHP')
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
                        'low_stock' => 'Low Stock',
                        'pre_order' => 'Pre-order',
                        'out_of_stock' => 'Out of Stock',
                    ])
                    ->label('Status')
                    ->modifyQueryUsing(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        if ($data['value'] === 'low_stock') {
                            return $query->whereHas('variants', function (Builder $q) {
                                $q->where('stock_quantity', '<=', 4);
                            });
                        }
                        
                        return $query->where('status', $data['value']);
                    }),

                SelectFilter::make('category')
                    ->relationship('category', 'name'),

                SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
                    
                SelectFilter::make('discounts')
                    ->relationship('discounts', 'name'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->url(fn (Product $record): string => route('filament.admin.resources.products.edit', ['record' => $record])),
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

    /**
     * Eager load the relationships for the table.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withTrashed()
            ->with(['brand', 'category', 'discounts']);
    }

    /**
     * Resolve a record route binding with the custom primary key.
     */
    public static function resolveRecordRouteBinding(int | string $key): ?Model
    {
        return static::getModel()::where('ProductID', $key)->withTrashed()->first();
    }
}
