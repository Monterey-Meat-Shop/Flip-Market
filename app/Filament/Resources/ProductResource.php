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
use Filament\Tables\Columns\BadgeColumn;
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
use Filament\Notifications\Notification;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

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
        return auth()->user()->hasRole(['admin', 'manager', 'cashier']);
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

    // 1) Instantly force uppercase while typing (no Livewire re-render)
    ->extraAttributes([
        'oninput' => 'this.value = this.value.toUpperCase()', // or: 'this.value = this.value.toLocaleUpperCase()'
        'style'   => 'text-transform: uppercase',              // visual cue
    ])

    // 2) Avoid per-keystroke Livewire updates (prevents caret jump)
    ->live(onBlur: true)

    // 3) Keep your slug logic (runs on blur/create)
    ->afterStateUpdated(function (string $operation, $state, Set $set) {
        if ($operation === 'create') {
            $set('slug', Str::slug($state));
        }
    })

    // 4) Persist uppercase safely at save/dehydrate
    ->dehydrateStateUsing(fn ($state) => mb_strtoupper((string) $state)),

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
                    
                    Repeater::make('variants')
                        ->label('Sizes & Stock')
                        ->relationship('variants')
                        ->schema([
                            TextInput::make('size')
                                ->required()
                                ->maxLength(225)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                    // Trigger merge check when size is changed
                                    static::mergeDuplicateSizes($get, $set);
                                }),
                            
                            TextInput::make('stock_quantity')
                                ->numeric()
                                ->rule('integer')
                                ->minValue(0)
                                ->required()
                                ->default(0)
                                ->validationMessages([
                                    'integer' => 'The stock quantity must be a whole number.',
                                    'min' => 'The stock quantity cannot be less than 0.',
                                ]),
                            
                            Select::make('colorway')
                                ->label('Colorway')
                                ->options([
                                    'Black' => 'Black',
                                    'White' => 'White',
                                    'Brown' => 'Brown',
                                    'Gray' => 'Gray',
                                    'Beige / Tan / Nude' => 'Beige / Tan / Nude',
                                    'Navy Blue' => 'Navy Blue',
                                    'Burgundy / Oxblood' => 'Burgundy / Oxblood',
                                    'Olive Green' => 'Olive Green',
                                    'Red' => 'Red',
                                    'Blue (general)' => 'Blue (general)',
                                    'Yellow' => 'Yellow',
                                    'Orange' => 'Orange',
                                    'Pink' => 'Pink',
                                    'Green (general)' => 'Green (general)',
                                    'Purple' => 'Purple',
                                    'Gold' => 'Gold',
                                    'Silver' => 'Silver',
                                    'Multi-color' => 'Multi-color',
                                ])
                                ->multiple()
                                ->searchable()
                                ->afterStateHydrated(function ($component, $state, string $operation) {
                                    // When editing, convert database string back to array
                                    if ($operation === 'edit' && is_string($state) && !empty($state)) {
                                        $colorwayArray = array_map('trim', explode(',', $state));
                                        $component->state($colorwayArray);
                                    }
                                })
                                ->dehydrateStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
                                ->required()
                                ->default(function (Get $get, string $operation) {
                                    // Only auto-fill for CREATE operation when adding new variant rows
                                    if ($operation !== 'create') {
                                        return null;
                                    }
                                    
                                    // Auto-fill colorway from first variant when adding new rows
                                    $variants = $get('../../variants');
                                    if (is_array($variants) && count($variants) > 0) {
                                        $firstColorway = $variants[0]['colorway'] ?? null;
                                        if ($firstColorway) {
                                            // If it's already an array, return as is
                                            if (is_array($firstColorway)) {
                                                return $firstColorway;
                                            }
                                            // If it's a string, convert to array
                                            if (is_string($firstColorway)) {
                                                return array_map('trim', explode(',', $firstColorway));
                                            }
                                        }
                                    }
                                    return null;
                                }),
                        ])
                        ->defaultItems(1)
                        ->columns(3)
                        ->columnSpanFull()
                        ->reorderable(false)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            // Trigger merge when items are added or removed
                            static::mergeDuplicateSizes($get, $set);
                        })
                        ->addActionLabel('Add Size')
                        ->live(),
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

                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pre_order' => 'Pre-order',
                                'in_stock' => 'In stock',
                            ])
                            ->disabled(function ($record, $get) {
                                return $record && $record->status === 'in_stock';
                            })
                            ->default('in_stock')
                            ->helperText('Status is calculated automatically unless set to Pre-order.'),

                                Toggle::make('is_active')
                                    ->required()
                                    ->default(true)
                                    ->helperText('Automatically managed, unless overridden for pre-order.'),
                    ]),
            ])->columnSpan(1)
        ])->columns(3);
    }

    /**
     * Merge duplicate sizes and combine their stock quantities
     */
    protected static function mergeDuplicateSizes(Get $get, Set $set): void
    {
        $variants = $get('variants');
        
        if (!is_array($variants) || empty($variants)) {
            return;
        }

        $mergedVariants = [];
        $sizeMap = [];
        $hasDuplicates = false;

        foreach ($variants as $index => $variant) {
            $size = trim($variant['size'] ?? '');
            
            // Skip empty sizes
            if (empty($size)) {
                $mergedVariants[] = $variant;
                continue;
            }

            $sizeLower = strtolower($size);

            if (isset($sizeMap[$sizeLower])) {
                // Found duplicate - merge stock quantities
                $hasDuplicates = true;
                $existingIndex = $sizeMap[$sizeLower];
                
                $existingStock = (int)($mergedVariants[$existingIndex]['stock_quantity'] ?? 0);
                $newStock = (int)($variant['stock_quantity'] ?? 0);
                $totalStock = $existingStock + $newStock;
                
                $mergedVariants[$existingIndex]['stock_quantity'] = $totalStock;
                
                // Keep the first colorway or merge if different
                $existingColorway = $mergedVariants[$existingIndex]['colorway'] ?? '';
                $newColorway = $variant['colorway'] ?? '';
                
                if ($newColorway && $newColorway !== $existingColorway) {
                    // Merge colorways if they're different
                    if (is_string($existingColorway)) {
                        $existingColorwayArray = array_filter(explode(', ', $existingColorway));
                    } else {
                        $existingColorwayArray = is_array($existingColorway) ? $existingColorway : [];
                    }
                    
                    if (is_string($newColorway)) {
                        $newColorwayArray = array_filter(explode(', ', $newColorway));
                    } else {
                        $newColorwayArray = is_array($newColorway) ? $newColorway : [];
                    }
                    
                    $combinedColorways = array_unique(array_merge($existingColorwayArray, $newColorwayArray));
                    $mergedVariants[$existingIndex]['colorway'] = implode(', ', $combinedColorways);
                }
            } else {
                // New size - add to merged list
                $sizeMap[$sizeLower] = count($mergedVariants);
                $mergedVariants[] = $variant;
            }
        }

        // Only update if we found duplicates
        if ($hasDuplicates) {
            $set('variants', array_values($mergedVariants));
            
            // Show notification
            Notification::make()
                ->title('Duplicate Sizes Merged')
                ->body('Same sizes have been combined and their stock quantities added together.')
                ->warning()
                ->duration(4000)
                ->send();
        }
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
                    ->getStateUsing(fn ($record) => image_url($record->image_url[0] ?? null))
                    ->extraImgAttributes([
                        'class' => 'cursor-pointer hover:scale-105 transition-transform duration-200',
                        'onclick' => "
                            event.stopPropagation();
                            const imgSrc = this.src;

                            // Create modal container
                            const modal = document.createElement('div');
                            modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-80 cursor-pointer';
                            modal.onclick = () => modal.remove();

                            // Create image element
                            const img = document.createElement('img');
                            img.src = imgSrc;
                            img.className = 'max-w-2xl max-h-[80vh] w-auto h-auto rounded-lg shadow-2xl object-contain';
                            img.onclick = (e) => e.stopPropagation();

                            modal.appendChild(img);
                            document.body.appendChild(modal);

                            // Listen for ESC key to close
                            const closeOnEsc = (e) => {
                                if (e.key === 'Escape') {
                                    modal.remove();
                                    document.removeEventListener('keydown', closeOnEsc);
                                }
                            };
                            document.addEventListener('keydown', closeOnEsc);
                        ",
                    ])
                    ->disableClick(), // prevents row navigation


                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'in_stock',
                        'warning' => 'low_stock',
                        'danger' => 'out_of_stock',
                        'info' => 'pre_order',
                    ])
                    ->sortable()
                    ->label('Status'),
                
                TextColumn::make('size_stocks')
                    ->label('Sizes & Stock')
                    ->getStateUsing(function ($record) {
                        if ($record->variants && $record->variants->isNotEmpty()) {
                            return $record->variants->map(function ($variant) {
                                return "Size {$variant->size}: {$variant->stock_quantity}";
                            })->implode(' | ');
                        }
                        return '-';
                    })
                    ->wrap(),

                TextColumn::make('colorways')
                    ->label('Colorways')
                    ->getStateUsing(function ($record) {
                        if ($record->variants && $record->variants->isNotEmpty()) {
                            $colorways = $record->variants->pluck('colorway')->filter()->unique()->implode(' | ');
                            return $colorways ?: '-';
                        }
                        return '-';
                    })
                    ->wrap()
                    ->lineClamp(2),

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
                    DeleteAction::make()
                        ->label('Archive')
                        ->modalHeading('Archive Product')
                        ->modalDescription('Are you sure you want to archive this product? You can restore it later if needed.')
                        ->modalSubmitActionLabel('Archive') 
                        ->modalCancelActionLabel('Cancel') 
                        ->color('danger')
                        ->icon('heroicon-o-archive-box')
                        ->visible(fn ($record) => $record->is_active === false),
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
        return parent::getEloquentQuery()
            ->withTrashed()
            ->with(['brand', 'category', 'discounts']);
    }

    public static function resolveRecordRouteBinding(int | string $key): ?Model
    {
        return static::getModel()::where('ProductID', $key)->withTrashed()->first();
    }

    public function getCalculatedStatusAttribute()
    {
        $totalStock = (int) $this->variants()->sum('stock_quantity');

        if ($this->status === 'pre_order') {
            return 'pre_order';
        }

        if ($totalStock === 0) {
            return 'out_of_stock';
        } elseif ($totalStock <= 4) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }
}