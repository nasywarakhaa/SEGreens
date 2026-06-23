<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required()
                    ->disabled(),
                Select::make('user_id')
                    ->relationship('user', 'full_name')
                    ->searchable()
                    ->preload()
                    ->disabled()
                    ->required(),
                Select::make('store_id')
                    ->relationship('store', 'name')
                    ->disabled()
                    ->required(),
                Select::make('user_address_id')
                    ->relationship('userAddress', 'label')
                    ->searchable()
                    ->preload()
                    ->disabled()
                    ->required(),
                Select::make('fulfillment_type_code')
                    ->options(FulfillmentType::options())
                    ->default(FulfillmentType::Delivery->value)
                    ->disabled()
                    ->required(),
                Select::make('status_code')
                    ->options(OrderStatus::options())
                    ->default(OrderStatus::Pending->value)
                    ->live()
                    ->native(false)
                    ->required(),
                Select::make('payment_status_code')
                    ->options(PaymentStatus::options())
                    ->default(PaymentStatus::Unpaid->value)
                    ->native(false)
                    ->required(),
                Textarea::make('note')
                    ->disabled()
                    ->columnSpanFull(),
                TextInput::make('cancel_reason')
                    ->required(fn ($get): bool => (int) $get('status_code') === OrderStatus::Cancelled->value),
                DateTimePicker::make('schedule_at'),
                TextInput::make('distance_km')
                    ->required()
                    ->disabled()
                    ->numeric()
                    ->default(0),
                TextInput::make('subtotal')
                    ->required()
                    ->disabled()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('delivery_fee')
                    ->required()
                    ->disabled()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('discount_amount')
                    ->required()
                    ->disabled()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('total_price')
                    ->required()
                    ->disabled()
                    ->numeric()
                    ->prefix('Rp'),
            ]);
    }
}
