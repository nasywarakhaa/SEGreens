<?php

namespace App\Filament\Resources\SystemSettings\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SystemSettingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('group_name')
                    ->label(__('admin.system_settings.fields.group_name')),
                TextEntry::make('key_name')
                    ->label(__('admin.system_settings.fields.key_name')),
                TextEntry::make('label')
                    ->label(__('admin.system_settings.fields.label')),
                TextEntry::make('value')
                    ->label(__('admin.system_settings.fields.value'))
                    ->placeholder(__('admin.common.not_available'))
                    ->columnSpanFull(),
                TextEntry::make('type')
                    ->label(__('admin.system_settings.fields.type'))
                    ->formatStateUsing(function (?string $state): string {
                        if (! filled($state)) {
                            return __('admin.common.not_available');
                        }

                        $translationKey = 'admin.system_settings.types.'.$state;
                        $translated = __($translationKey);

                        return $translated === $translationKey ? $state : $translated;
                    }),
                IconEntry::make('is_encrypted')
                    ->label(__('admin.system_settings.fields.is_encrypted'))
                    ->boolean(),
                IconEntry::make('is_active')
                    ->label(__('admin.system_settings.fields.is_active'))
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
