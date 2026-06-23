<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: int implements HasColor, HasLabel
{
    case Pending = 1;
    case Confirmed = 2;
    case Packed = 3;
    case OnDelivery = 4;
    case Completed = 5;
    case Cancelled = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => __('admin.enums.order_status.pending'),
            self::Confirmed => __('admin.enums.order_status.confirmed'),
            self::Packed => __('admin.enums.order_status.packed'),
            self::OnDelivery => __('admin.enums.order_status.on_delivery'),
            self::Completed => __('admin.enums.order_status.completed'),
            self::Cancelled => __('admin.enums.order_status.cancelled'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'info',
            self::Packed => 'primary',
            self::OnDelivery => 'success',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->getLabel()])
            ->all();
    }

    /**
     * @return array<int, array<int>>
     */
    public static function transitionMap(): array
    {
        return [
            self::Pending->value => [self::Confirmed->value, self::Cancelled->value],
            self::Confirmed->value => [self::Packed->value, self::Cancelled->value],
            self::Packed->value => [self::OnDelivery->value, self::Completed->value],
            self::OnDelivery->value => [self::Completed->value],
            self::Completed->value => [],
            self::Cancelled->value => [],
        ];
    }

    public static function canTransition(int $from, int $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return in_array($to, self::transitionMap()[$from] ?? [], true);
    }

    /**
     * @return array<int>
     */
    public static function terminalValues(): array
    {
        return [
            self::Completed->value,
            self::Cancelled->value,
        ];
    }
}
