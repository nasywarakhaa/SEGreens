<?php

namespace App\Filament\Resources\UserAddresses\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;

class UserAddressInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.id')
                    ->label(__('admin.user_address.fields.user')),
                TextEntry::make('label'),
                TextEntry::make('recipient_name'),
                PhoneEntry::make('phone_number'),
                TextEntry::make('address')
                    ->columnSpanFull(),
                TextEntry::make('address_note')
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('postal_code')
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('latitude')
                    ->numeric(),
                TextEntry::make('longitude')
                    ->numeric(),
                IconEntry::make('is_default')
                    ->boolean(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
            ]);
    }
}
