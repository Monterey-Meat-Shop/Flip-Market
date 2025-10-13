<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Filament\Forms\Get;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Settings';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make("User Information")->schema([

                    // First Name
                    TextInput::make('name')
                        ->label('First Name')
                        ->maxLength(255)
                        ->required(),

                    // Last Name
                    TextInput::make('last_name')
                        ->label('Last Name')
                        ->maxLength(255),

                    // Email
                    TextInput::make('email')
                        ->email()
                        ->unique(ignorable: fn ($record) => $record)
                        ->required()
                        ->maxLength(255),

                    DateTimePicker::make('email_verified_at')
                        ->default(now())
                        ->dehydrated(true),

                    TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->maxLength(255)
                        ->same('password_confirmation')
                        ->validationAttribute('password'),

                    TextInput::make('password_confirmation')
                        ->label('Confirm Password')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->maxLength(255)
                        ->dehydrated(false)
                        ->validationAttribute('password confirmation')
                        ->rules([
                            fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                if ($get('password') !== $value) {
                                    $fail('The password confirmation does not match.');
                                }
                            },
                        ]),

                    Select::make('roles')
                        ->relationship(
                            'roles',
                            'name',
                            fn (Builder $query) => $query->where('name', '!=', 'admin')
                        )
                        ->preload()
                        ->required()
                        ->live(),
                ])
                ->columns(2),

                // Address fields (for customers only) - Now using direct user fields
                Card::make("Customer Information")
                    ->schema([
                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->maxLength(10),

                        TextInput::make('address_line_1')
                            ->label('Address Line 1')
                            ->maxLength(255),

                        TextInput::make('city')
                            ->label('City')
                            ->maxLength(100),

                        TextInput::make('address_line_2')
                            ->label('Address Line 2')
                            ->maxLength(255),

                        TextInput::make('province')
                            ->label('Province')
                            ->maxLength(100),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get) => self::isCustomerRole($get)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),

                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->getStateUsing(fn (User $record): string => $record->roles->pluck('name')->implode(', ')),

                TextColumn::make('address_line_1')
                    ->label('Address')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('city')
                    ->label('City')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('province')
                    ->label('Province')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('postal_code')
                    ->label('Postal Code')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (User $record): string => $record->is_active ? 'Active' : 'Inactive')
                    ->colors([
                        'success' => fn ($state): bool => $state === 'Active',
                        'danger' => fn ($state): bool => $state === 'Inactive',
                    ]),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn () => request()->routeIs('*.trashed')),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active users only')
                    ->falseLabel('Inactive users only')
                    ->native(false),

                Tables\Filters\Filter::make('email_verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at'))
                    ->label('Email Verified'),
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
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [OrdersRelationManager::class,];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withTrashed(); // Include soft deleted records for admin
    }

    public static function resolveRecordRouteBinding(int | string $key): ?\App\Models\User
    {
        return static::getModel()::withTrashed()->find($key);
    }

    /**
     * Helper: check if selected role is customer
     */
    private static function isCustomerRole(Get $get): bool
    {
        $roleIds = $get('roles');
        
        // Handle if roles is null or empty
        if (empty($roleIds)) {
            return false;
        }
        
        // If roles is a single value, convert to array
        if (!is_array($roleIds)) {
            $roleIds = [$roleIds];
        }
        
        // Get customer role ID
        $customerRoleId = Role::where('name', 'customer')->value('id');
        
        return in_array($customerRoleId, $roleIds);
    }
}