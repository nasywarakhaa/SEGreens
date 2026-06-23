<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;

class StoreInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('description')
                    ->placeholder(__('admin.common.not_available'))
                    ->columnSpanFull(),
                TextEntry::make('address')
                    ->columnSpanFull(),
                TextEntry::make('latitude')
                    ->numeric(),
                TextEntry::make('longitude')
                    ->numeric(),
                TextEntry::make('service_radius_km')
                    ->numeric(),
                TextEntry::make('base_delivery_fee')
                    ->money('IDR'),
                PhoneEntry::make('phone_number'),
                TextEntry::make('logo')
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('open_time')
                    ->time()
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('close_time')
                    ->time()
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
            ]);
    }
}
