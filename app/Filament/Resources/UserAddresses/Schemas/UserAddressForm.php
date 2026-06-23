<?php

namespace App\Filament\Resources\UserAddresses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class UserAddressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(__('admin.user_address.fields.user'))
                    ->relationship('user', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('label')
                    ->label(__('admin.user_address.fields.label'))
                    ->required(),
                TextInput::make('recipient_name')
                    ->label(__('admin.user_address.fields.recipient_name'))
                    ->required(),
                PhoneInput::make('phone_number')
                    ->label(__('admin.user_address.fields.phone_number'))
                    ->defaultCountry('ID')
                    ->separateDialCode()
                    ->validateFor(country: 'ID', lenient: true)
                    ->required(),
                Textarea::make('address')
                    ->label(__('admin.user_address.fields.address'))
                    ->required()
                    ->columnSpanFull(),
                Grid::make(2)->schema([
                    TextInput::make('latitude')
                        ->label(__('admin.user_address.fields.latitude'))
                        ->required()
                        ->numeric()
                        ->step('0.0000001')
                        ->helperText(__('admin.store.helpers.latitude_decimal')),
                    TextInput::make('longitude')
                        ->label(__('admin.user_address.fields.longitude'))
                        ->required()
                        ->numeric()
                        ->step('0.0000001')
                        ->helperText(__('admin.store.helpers.longitude_decimal')),
                ]),
                ViewField::make('location_map')
                    ->label(__('admin.user_address.fields.location_map'))
                    ->view('filament.forms.components.osm-map')
                    ->viewData([
                        'latStatePath' => 'data.latitude',
                        'lngStatePath' => 'data.longitude',
                        'addressStatePath' => 'data.address',
                    ])
                    ->columnSpanFull()
                    ->dehydrated(false),
                TextInput::make('address_note')
                    ->label(__('admin.user_address.fields.address_note')),
                TextInput::make('postal_code')
                    ->label(__('admin.user_address.fields.postal_code')),
                Toggle::make('is_default')
                    ->label(__('admin.user_address.fields.is_default'))
                    ->default(false)
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('admin.user_address.fields.is_active'))
                    ->default(true)
                    ->required(),
            ]);
    }
}
