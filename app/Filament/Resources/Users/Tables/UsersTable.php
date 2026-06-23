<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('admin.users.fields.email'))
                    ->searchable(),
                PhoneColumn::make('phone_number')
                    ->searchable(),
                TextColumn::make('role_code')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state?->label() ?? '-')
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        UserRole::Superuser->value => 'danger',
                        UserRole::Admin->value => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status_code')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state?->label() ?? '-')
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        UserStatus::Active->value => 'success',
                        UserStatus::Inactive->value => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role_code')
                    ->options(UserRole::options()),
                SelectFilter::make('status_code')
                    ->options(UserStatus::options()),
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
