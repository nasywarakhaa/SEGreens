<?php

namespace App\Enums;

enum CartStatus: int
{
    case Active = 1;
    case CheckedOut = 2;
    case Abandoned = 3;

    public function label(): string
    {
        return match ($this) {
            self::Active => __('admin.enums.cart_status.active'),
            self::CheckedOut => __('admin.enums.cart_status.checked_out'),
            self::Abandoned => __('admin.enums.cart_status.abandoned'),
        };
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
