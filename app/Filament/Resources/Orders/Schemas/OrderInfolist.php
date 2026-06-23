<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order_number'),
                TextEntry::make('user.id')
                    ->label(__('admin.orders.fields.user')),
                TextEntry::make('store.name')
                    ->label(__('admin.orders.fields.store')),
                TextEntry::make('userAddress.id')
                    ->label(__('admin.orders.fields.user_address')),
                TextEntry::make('fulfillment_type_code')
                    ->badge(),
                TextEntry::make('status_code')
                    ->badge(),
                TextEntry::make('payment_status_code')
                    ->badge(),
                TextEntry::make('note')
                    ->placeholder(__('admin.common.not_available'))
                    ->columnSpanFull(),
                TextEntry::make('cancel_reason')
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('schedule_at')
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('distance_km')
                    ->numeric(),
                TextEntry::make('subtotal')
                    ->money('IDR'),
                TextEntry::make('delivery_fee')
                    ->money('IDR'),
                TextEntry::make('discount_amount')
                    ->money('IDR'),
                TextEntry::make('total_price')
                    ->money('IDR'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
            ]);
    }
}
