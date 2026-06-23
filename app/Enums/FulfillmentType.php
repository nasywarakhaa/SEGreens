<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FulfillmentType: int implements HasColor, HasLabel
{
    case Delivery = 1;
    case Pickup = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::Delivery => __('admin.enums.fulfillment_type.delivery'),
            self::Pickup => __('admin.enums.fulfillment_type.pickup'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Delivery => 'info',
            self::Pickup => 'gray',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->getLabel()])
            ->all();
    }
}
