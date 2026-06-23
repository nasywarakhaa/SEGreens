<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('slug'),
                ImageEntry::make('image')
                    ->label(__('admin.product_categories.fields.image'))
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('description')
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('sort_order')
                    ->numeric(),
                IconEntry::make('is_active')
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
