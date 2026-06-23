<?php

namespace App\Filament\Resources\SystemSettings\Tables;

use App\Models\SystemSetting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SystemSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group_name')
                    ->label(__('admin.system_settings.fields.group_name'))
                    ->searchable(),
                TextColumn::make('key_name')
                    ->label(__('admin.system_settings.fields.key_name'))
                    ->searchable(),
                TextColumn::make('label')
                    ->label(__('admin.system_settings.fields.label'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('admin.system_settings.fields.type'))
                    ->formatStateUsing(function (?string $state): string {
                        if (! filled($state)) {
                            return __('admin.common.not_available');
                        }

                        $translationKey = 'admin.system_settings.types.'.$state;
                        $translated = __($translationKey);

                        return $translated === $translationKey ? $state : $translated;
                    })
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label(__('admin.system_settings.fields.is_active'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('group_name')
                    ->label(__('admin.system_settings.filters.group_name'))
                    ->options(fn (): array => SystemSetting::query()
                        ->distinct()
                        ->orderBy('group_name')
                        ->pluck('group_name', 'group_name')
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
