<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $nextStatus = (int) ($data['status_code'] ?? 0);
        $nextPaymentStatus = (int) ($data['payment_status_code'] ?? 0);

        if (
            in_array($nextStatus, [OrderStatus::Packed->value, OrderStatus::OnDelivery->value, OrderStatus::Completed->value], true)
            && $nextPaymentStatus !== PaymentStatus::Paid->value
        ) {
            throw ValidationException::withMessages([
                'status_code' => [__('admin.orders.messages.payment_required')],
            ]);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
