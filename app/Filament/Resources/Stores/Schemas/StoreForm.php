<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(6)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('logo')
                            ->label(__('admin.store.fields.logo'))
                            ->disk('public')
                            ->directory('stores/logos')
                            ->image()
                            ->panelLayout('integrated')
                            ->panelAspectRatio('1:1')
                            ->imagePreviewHeight('180')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth(600)
                            ->imageResizeTargetHeight(600)
                            ->columnSpan(2),
                        Grid::make(1)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('admin.store.fields.name'))
                                    ->required(),
                                Textarea::make('description')
                                    ->label(__('admin.store.fields.description'))
                                    ->rows(4),
                            ])
                            ->columnSpan(4),
                    ]),
                Grid::make(5)
                    ->columnSpanFull()
                    ->schema([
                        PhoneInput::make('phone_number')
                            ->label(__('admin.store.fields.phone_number'))
                            ->defaultCountry('ID')
                            ->separateDialCode()
                            ->validateFor(country: 'ID', lenient: true)
                            ->required(),
                        TimePicker::make('open_time')
                            ->label(__('admin.store.fields.open_time')),
                        TimePicker::make('close_time')
                            ->label(__('admin.store.fields.close_time')),
                        TextInput::make('service_radius_km')
                            ->label(__('admin.store.fields.service_radius_m'))
                            ->required()
                            ->numeric()
                            ->dehydrateStateUsing(fn ($state) => $state !== null ? ((float) $state / 1000) : null)
                            ->formatStateUsing(fn ($state) => $state !== null ? (float) $state * 1000 : null),
                        TextInput::make('base_delivery_fee')
                            ->label(__('admin.store.fields.base_delivery_fee'))
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                    ]),
                Textarea::make('address')
                    ->label(__('admin.store.fields.address'))
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('latitude')
                            ->label(__('admin.store.fields.latitude'))
                            ->required()
                            ->numeric()
                            ->helperText(__('admin.store.helpers.latitude_decimal')),
                        TextInput::make('longitude')
                            ->label(__('admin.store.fields.longitude'))
                            ->required()
                            ->numeric()
                            ->helperText(__('admin.store.helpers.longitude_decimal')),
                    ]),
                ViewField::make('location_map')
                    ->label(__('admin.store.fields.location_map'))
                    ->view('filament.forms.components.osm-map')
                    ->viewData([
                        'latStatePath' => 'data.latitude',
                        'lngStatePath' => 'data.longitude',
                        'addressStatePath' => 'data.address',
                    ])
                    ->columnSpanFull()
                    ->dehydrated(false),
            ]);
    }
}
