<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product_category_id')
                    ->numeric(),
                TextEntry::make('sku')
                    ->label(__('admin.products.fields.sku'))
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('description')
                    ->placeholder(__('admin.common.not_available'))
                    ->columnSpanFull(),
                ImageEntry::make('image')
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('price')
                    ->money('IDR'),
                TextEntry::make('stock')
                    ->numeric(),
                TextEntry::make('sell_count')
                    ->numeric(),
                TextEntry::make('weight')
                    ->numeric(),
                TextEntry::make('unit'),
                TextEntry::make('min_order_qty')
                    ->numeric(),
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
