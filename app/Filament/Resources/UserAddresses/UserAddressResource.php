<?php

namespace App\Filament\Resources\UserAddresses;

use App\Filament\Resources\UserAddresses\Pages\ListUserAddresses;
use App\Filament\Resources\UserAddresses\Pages\ViewUserAddress;
use App\Filament\Resources\UserAddresses\Schemas\UserAddressForm;
use App\Filament\Resources\UserAddresses\Schemas\UserAddressInfolist;
use App\Filament\Resources\UserAddresses\Tables\UserAddressesTable;
use App\Models\UserAddress;
use App\Support\AdminPanelAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserAddressResource extends Resource
{
    protected static ?string $model = UserAddress::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return UserAddressForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserAddressInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserAddressesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canViewAny(): bool
    {
        return AdminPanelAccess::isSuperuser();
    }

    public static function canView($record): bool
    {
        return AdminPanelAccess::isSuperuser();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserAddresses::route('/'),
            'view' => ViewUserAddress::route('/{record}'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.access');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.resources.user_addresses.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.user_addresses.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.user_addresses.plural');
    }
}
