<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    public function __construct(
        public readonly Order $order,
        public readonly int $fromStatus,
        public readonly int $toStatus,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = $this->appName();

        return (new MailMessage)
            ->subject('Status Pesanan Diperbarui: '.$this->order->order_number)
            ->greeting('Halo '.$this->order->user?->full_name.',')
            ->line('Status pesanan Anda telah berubah.')
            ->line('Nomor pesanan: '.$this->order->order_number)
            ->line('Dari: '.$this->statusLabel($this->fromStatus))
            ->line('Menjadi: '.$this->statusLabel($this->toStatus))
            ->line('Total: Rp '.number_format((float) $this->order->total_price, 0, ',', '.'))
            ->line('Terima kasih telah berbelanja di '.$appName.'.')
            ->salutation("Salam,\nTim {$appName}");
    }

    private function statusLabel(int $code): string
    {
        $status = OrderStatus::tryFrom($code);

        return $status?->getLabel() ?? (string) $code;
    }

    private function appName(): string
    {
        $name = trim((string) (config('mail.from.name') ?: config('app.name', '')));
        if ($name === '' || strcasecmp($name, 'Laravel') === 0) {
            return 'SEGreens';
        }

        return $name;
    }
}
