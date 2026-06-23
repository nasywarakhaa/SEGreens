<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('full_name'),
                TextEntry::make('email')
                    ->label(__('admin.users.fields.email')),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
                PhoneEntry::make('phone_number'),
                TextEntry::make('avatar')
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('role_code')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof UserRole ? $state->label() : UserRole::tryFrom((int) $state)?->label()),
                TextEntry::make('status_code')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof UserStatus ? $state->label() : UserStatus::tryFrom((int) $state)?->label()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
            ]);
    }
}
