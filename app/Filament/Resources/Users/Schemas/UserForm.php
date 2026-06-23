<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class UserForm
{
    private static function isEditingOwnUser(?User $record): bool
    {
        $authId = auth()->id();

        return $record instanceof User
            && is_string($authId)
            && $authId !== ''
            && $authId === (string) $record->getKey();
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('full_name')
                    ->visible(fn (string $operation, ?User $record): bool => $operation === 'create' || self::isEditingOwnUser($record))
                    ->required(fn (string $operation, ?User $record): bool => $operation === 'create' || self::isEditingOwnUser($record)),
                TextInput::make('email')
                    ->label(__('admin.users.fields.email'))
                    ->autocomplete('off')
                    ->extraInputAttributes(['autocomplete' => 'new-email'])
                    ->visible(fn (string $operation, ?User $record): bool => $operation === 'create' || self::isEditingOwnUser($record))
                    ->email()
                    ->required(fn (string $operation, ?User $record): bool => $operation === 'create' || self::isEditingOwnUser($record))
                    ->unique(ignoreRecord: true),
                TextInput::make('username')
                    ->label(__('admin.users.fields.username'))
                    ->visible(fn (string $operation, ?User $record): bool => $operation === 'edit' && self::isEditingOwnUser($record))
                    ->maxLength(50)
                    ->regex('/^[a-zA-Z0-9._]+$/')
                    ->unique(ignoreRecord: true),
                Toggle::make('is_email_verified')
                    ->label(__('admin.users.fields.is_email_verified'))
                    ->visible(fn (string $operation, ?User $record): bool => $operation === 'edit' && ! self::isEditingOwnUser($record) && blank($record?->email_verified_at))
                    ->dehydrated(fn (?User $record): bool => blank($record?->email_verified_at))
                    ->default(false)
                    ->helperText(__('admin.users.helpers.toggle_to_verify_email')),
                Placeholder::make('email_verified_info')
                    ->label(__('admin.users.fields.is_email_verified'))
                    ->visible(fn (string $operation, ?User $record): bool => $operation === 'edit' && ! self::isEditingOwnUser($record) && filled($record?->email_verified_at))
                    ->content(__('admin.users.helpers.email_already_verified')),
                PhoneInput::make('phone_number')
                    ->visible(fn (string $operation, ?User $record): bool => $operation === 'create' || self::isEditingOwnUser($record))
                    ->defaultCountry('ID')
                    ->separateDialCode()
                    ->validateFor(country: 'ID', lenient: true)
                    ->required(fn (string $operation, ?User $record): bool => $operation === 'create' || self::isEditingOwnUser($record)),
                FileUpload::make('avatar')
                    ->visible(fn (string $operation, ?User $record): bool => $operation === 'create' || self::isEditingOwnUser($record))
                    ->disk('public')
                    ->directory('users/avatars')
                    ->image(),
                Select::make('role_code')
                    ->label(__('admin.users.fields.role'))
                    ->options(UserRole::options())
                    ->default(UserRole::User->value)
                    ->native(false)
                    ->required(),
                Select::make('status_code')
                    ->label(__('admin.users.fields.status'))
                    ->options(UserStatus::options())
                    ->default(UserStatus::Active->value)
                    ->native(false)
                    ->required(),
                TextInput::make('password')
                    ->label(__('admin.users.fields.password'))
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->extraInputAttributes(['autocomplete' => 'new-password'])
                    ->helperText(fn (string $operation): ?string => $operation === 'edit'
                        ? __('admin.users.helpers.password_optional_on_edit')
                        : null)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
            ]);
    }
}
