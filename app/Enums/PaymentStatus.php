<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: int implements HasColor, HasLabel
{
    case Unpaid = 1;
    case Paid = 2;
    case Failed = 3;
    case Refunded = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Unpaid => __('admin.enums.payment_status.unpaid'),
            self::Paid => __('admin.enums.payment_status.paid'),
            self::Failed => __('admin.enums.payment_status.failed'),
            self::Refunded => __('admin.enums.payment_status.refunded'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Unpaid => 'warning',
            self::Paid => 'success',
            self::Failed => 'danger',
            self::Refunded => 'gray',
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
}
