<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product.name')
                    ->label(__('admin.orders.fields.product'))
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('product_name'),
                ImageEntry::make('product_image')
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('product_weight')
                    ->numeric(),
                TextEntry::make('product_unit'),
                TextEntry::make('price')
                    ->money('IDR'),
                TextEntry::make('qty')
                    ->numeric(),
                TextEntry::make('subtotal')
                    ->money('IDR'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->searchable(),
                ImageColumn::make('product_image')
                    ->disk('public'),
                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(false),
                ]),
            ]);
    }
}
