<?php

namespace App\Filament\Resources\SystemSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SystemSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('group_name')
                    ->label(__('admin.system_settings.fields.group_name'))
                    ->required(),
                TextInput::make('key_name')
                    ->label(__('admin.system_settings.fields.key_name'))
                    ->required(),
                TextInput::make('label')
                    ->label(__('admin.system_settings.fields.label'))
                    ->required(),
                Textarea::make('value')
                    ->label(__('admin.system_settings.fields.value'))
                    ->columnSpanFull(),
                Select::make('type')
                    ->label(__('admin.system_settings.fields.type'))
                    ->options([
                        'string' => __('admin.system_settings.types.string'),
                        'integer' => __('admin.system_settings.types.integer'),
                        'boolean' => __('admin.system_settings.types.boolean'),
                        'json' => __('admin.system_settings.types.json'),
                        'password' => __('admin.system_settings.types.password'),
                    ])
                    ->native(false)
                    ->required()
                    ->default('string'),
                Toggle::make('is_encrypted')
                    ->label(__('admin.system_settings.fields.is_encrypted'))
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('admin.system_settings.fields.is_active'))
                    ->required(),
            ]);
    }
}
