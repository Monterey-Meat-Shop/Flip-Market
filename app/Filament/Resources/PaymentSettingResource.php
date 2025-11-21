<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentSettingResource\Pages;
use App\Models\PaymentSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentSettingResource extends Resource
{
    protected static ?string $model = PaymentSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Store Settings';
    protected static ?string $navigationLabel = 'Payment Settings';

    /** Only 1 record allowed — disable create button */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('GCash Settings')
                    ->schema([
                        Forms\Components\TextInput::make('gcash_account_name')
                            ->label('GCash Account Name'),

                        Forms\Components\TextInput::make('gcash_number')
                            ->label('GCash Number'),

                        Forms\Components\FileUpload::make('gcash_qr')
                            ->label('GCash QR Code')
                            ->image()
                            ->directory('payment_settings'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Bank Transfer Settings')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Bank Name'),

                        Forms\Components\TextInput::make('bank_account_name')
                            ->label('Bank Account Name'),

                        Forms\Components\TextInput::make('bank_account_number')
                            ->label('Bank Account Number'),

                        Forms\Components\FileUpload::make('bank_qr')
                            ->label('Bank QR Code')
                            ->image()
                            ->directory('payment_settings'),
                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('gcash_account_name')->label('GCash Name'),
                Tables\Columns\TextColumn::make('gcash_number')->label('GCash Number'),

                Tables\Columns\TextColumn::make('bank_name')->label('Bank'),
                Tables\Columns\TextColumn::make('bank_account_number')->label('Account Number'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]); // No bulk actions
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentSettings::route('/'),
            'edit' => Pages\EditPaymentSetting::route('/{record}/edit'),
        ];
    }
}
