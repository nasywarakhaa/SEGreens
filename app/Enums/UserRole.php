<?php

namespace App\Enums;

enum UserRole: int
{
    case Superuser = 1;
    case Admin = 2;
    case User = 3;

    public function label(): string
    {
        return match ($this) {
            self::Superuser => __('admin.enums.user_role.superuser'),
            self::Admin => __('admin.enums.user_role.admin'),
            self::User => __('admin.enums.user_role.user'),
        };
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role): array => [$role->value => $role->label()])
            ->all();
    }
}
