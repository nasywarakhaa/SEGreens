<?php

namespace App\Jobs;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;
use App\Services\FcmService;
use App\Services\RuntimeMailConfigService;
use App\Services\SystemSettingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendOrderStatusNotifications implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly string $orderId,
        public readonly int $fromStatus,
        public readonly int $toStatus,
    ) {}

    public function handle(
        SystemSettingService $settings,
        RuntimeMailConfigService $mailConfig,
        FcmService $fcm,
    ): void {
        $order = Order::query()
            ->with('user')
            ->find($this->orderId);

        if (! $order || ! $order->user) {
            return;
        }

        $statusLabel = OrderStatus::tryFrom($this->toStatus)?->getLabel() ?? ('Status #'.$this->toStatus);

        if (
            $settings->getBool('app', 'enable_order_email_notification', true)
            && $mailConfig->isConfigured()
        ) {
            $mailConfig->applyFromSettings();

            try {
                $order->user->notify(new OrderStatusChangedNotification(
                    order: $order,
                    fromStatus: $this->fromStatus,
                    toStatus: $this->toStatus,
                ));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        if (
            $settings->getBool('app', 'enable_order_push_notification', true)
            && is_string($order->user->fcm_token)
            && trim($order->user->fcm_token) !== ''
        ) {
            try {
                $fcm->sendToToken(
                    token: (string) $order->user->fcm_token,
                    title: 'Status Pesanan Diperbarui',
                    body: 'Pesanan '.$order->order_number.' sekarang: '.$statusLabel,
                    data: [
                        'type' => 'order_status',
                        'order_id' => (string) $order->id,
                        'order_number' => (string) $order->order_number,
                        'status_code' => $this->toStatus,
                    ],
                );
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
