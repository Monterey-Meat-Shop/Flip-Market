<?php

namespace App\Filament\Resources;

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

                    // First Name (for customers only)
                    TextInput::make('name')
                        ->label('First Name')
                        ->maxLength(255),

                    // Last Name (for customers only)
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
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->maxLength(255),

                    Select::make('roles')
                        ->relationship(
                            'roles',
                            'name',
                            fn (Builder $query) => $query->where('name', '!=', 'admin')
                        )
                        ->preload()
                        ->required()
                        ->reactive(),
                ])
                ->columns(2),

                // Address fields (for customers only)
                Card::make("Customer Information")
                    ->schema([
                        TextInput::make('phone')
                        ->label('Phone')
                        ->tel()
                        ->maxLength(20),

                        TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->customer?->address?->first()?->postal_code ?? '')
                            ),

                        TextInput::make('address_line_1')
                            ->label('Address Line 1')
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->customer?->address?->first()?->address_line_1 ?? '')
                            ),

                        TextInput::make('city')
                            ->label('City')
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->customer?->address?->first()?->city ?? '')
                            ),

                        TextInput::make('address_line_2')
                            ->label('Address Line 2')
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->customer?->address?->first()?->address_line_2 ?? '')
                            ),

                        TextInput::make('province')
                            ->label('Province')
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->customer?->address?->first()?->province ?? '')
                            ),

                        
                    ])
                    ->columns(2)
                    ->visible(fn (callable $get) => self::isCustomerRole($get)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),

                // Phone from customers table
                // TextColumn::make('customer.phone')
                //     ->label('Phone')
                //     ->searchable(),

                TextColumn::make('email')->searchable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->getStateUsing(fn (User $record): string => $record->roles->pluck('name')->implode(', ')),

                // Address (show first address only)
                // TextColumn::make('customer.address.address_line_1')
                //     ->label('Address')
                //     ->getStateUsing(fn (User $record): string =>
                //         $record->customer?->address->first()?->address_line_1 ?? 'N/A'
                //     ),

                // TextColumn::make('customer.address.city')
                //     ->label('City')
                //     ->getStateUsing(fn (User $record): string =>
                //         $record->customer?->address->first()?->city ?? 'N/A'
                //     ),

                // TextColumn::make('customer.address.province')
                //     ->label('Province')
                //     ->getStateUsing(fn (User $record): string =>
                //         $record->customer?->address->first()?->province ?? 'N/A'
                //     ),

                // TextColumn::make('customer.address.postal_code')
                //     ->label('Postal Code')
                //     ->getStateUsing(fn (User $record): string =>
                //         $record->customer?->address->first()?->postal_code ?? 'N/A'
                //     ),

                TextColumn::make('email_verified_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->hidden(fn ($livewire) => $livewire->activeTab !== 'archived'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
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
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
        return parent::getEloquentQuery()->with(['customer.address']);
    }

    public static function resolveRecordRouteBinding(int | string $key): ?\App\Models\User
    {
        return static::getModel()::withTrashed()->find($key);
    }

    /**
     * Helper: check if selected role is customer
     */
    private static function isCustomerRole(callable $get): bool
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