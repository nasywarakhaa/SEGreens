<?php

namespace App\Enums;

enum UserStatus: int
{
    case Active = 1;
    case Inactive = 2;
    case Banned = 3;

    public function label(): string
    {
        return match ($this) {
            self::Active => __('admin.enums.user_status.active'),
            self::Inactive => __('admin.enums.user_status.inactive'),
            self::Banned => __('admin.enums.user_status.banned'),
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
