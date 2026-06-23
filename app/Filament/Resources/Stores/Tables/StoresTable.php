<?php

namespace App\Filament\Resources\Stores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('service_radius_km')
                    ->label(__('admin.store.fields.service_radius_short'))
                    ->formatStateUsing(fn ($state): string => $state === null ? '-' : (string) (int) round($state * 1000))
                    ->sortable(),
                TextColumn::make('base_delivery_fee')
                    ->label(__('admin.store.fields.base_delivery_fee_short'))
                    ->money('IDR')
                    ->sortable(),
                PhoneColumn::make('phone_number')
                    ->label(__('admin.store.fields.phone_number'))
                    ->searchable(),
                TextColumn::make('open_time')
                    ->label(__('admin.store.fields.open_time'))
                    ->time()
                    ->sortable(),
                TextColumn::make('close_time')
                    ->label(__('admin.store.fields.close_time'))
                    ->time()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(false),
                ]),
            ]);
    }
}
