<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class MidtransService
{
    public function isConfigured(): bool
    {
        return $this->merchantId() !== ''
            && $this->clientKey() !== ''
            && $this->serverKey() !== '';
    }

    public function merchantId(): string
    {
        return (string) config('midtrans.merchant_id', '');
    }

    public function clientKey(): string
    {
        return (string) config('midtrans.client_key', '');
    }

    public function createSnapTransaction(Order $order): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Midtrans is not configured.');
        }

        $order->loadMissing(['items', 'user', 'userAddress']);

        $payload = [
            'transaction_details' => [
                'order_id' => (string) $order->order_number,
                'gross_amount' => $this->toIntAmount($order->total_price),
            ],
            'item_details' => $this->buildItemDetails($order),
            'customer_details' => [
                'first_name' => (string) $order->user?->full_name,
                'email' => (string) $order->user?->email,
                'phone' => (string) $order->userAddress?->phone_number,
                'billing_address' => [
                    'first_name' => (string) $order->userAddress?->recipient_name,
                    'phone' => (string) $order->userAddress?->phone_number,
                    'address' => (string) $order->userAddress?->address,
                ],
                'shipping_address' => [
                    'first_name' => (string) $order->userAddress?->recipient_name,
                    'phone' => (string) $order->userAddress?->phone_number,
                    'address' => (string) $order->userAddress?->address,
                ],
            ],
        ];

        $finishRedirectUrl = trim((string) config('midtrans.finish_redirect_url', ''));
        if ($finishRedirectUrl !== '') {
            $payload['callbacks'] = [
                'finish' => $finishRedirectUrl,
            ];
        }

        $response = Http::acceptJson()
            ->withBasicAuth($this->serverKey(), '')
            ->timeout(30)
            ->post($this->apiBaseUrl().'/snap/v1/transactions', $payload);

        if (! $response->successful()) {
            $message = (string) ($response->json('error_messages.0')
                ?? $response->json('status_message')
                ?? 'Failed to create Midtrans transaction.');

            throw new RuntimeException($message);
        }

        return [
            'token' => (string) $response->json('token'),
            'redirect_url' => (string) $response->json('redirect_url'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchTransactionStatus(string $orderNumber): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Midtrans is not configured.');
        }

        $response = Http::acceptJson()
            ->withBasicAuth($this->serverKey(), '')
            ->timeout(30)
            ->get($this->apiBaseUrl().'/v2/'.urlencode($orderNumber).'/status');

        if (! $response->successful()) {
            $message = (string) ($response->json('status_message') ?? 'Failed to fetch Midtrans status.');
            throw new RuntimeException($message);
        }

        /** @var array<string, mixed> */
        return $response->json();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function verifyNotificationSignature(array $payload): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '') {
            return false;
        }

        $localSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey());

        return hash_equals($localSignature, $signatureKey);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function applyNotificationToOrder(Order $order, array $payload): Order
    {
        $currentStatus = (int) ($order->status_code?->value ?? $order->status_code);
        if (in_array($currentStatus, OrderStatus::terminalValues(), true)) {
            return $order;
        }

        $paymentStatus = $this->mapPaymentStatus($payload);
        $transactionStatus = strtolower((string) ($payload['transaction_status'] ?? ''));
        $paymentType = (string) ($payload['payment_type'] ?? '');

        $updates = [
            'payment_provider' => 'midtrans',
            'payment_status_code' => $paymentStatus->value,
            'payment_reference' => (string) ($payload['transaction_id'] ?? $order->payment_reference),
            'payment_method' => $paymentType === '' ? $order->payment_method : $paymentType,
            'payment_channel' => $this->extractPaymentChannel($payload),
        ];

        if ($paymentStatus === PaymentStatus::Paid && ! $order->paid_at) {
            $updates['paid_at'] = now();
        }

        if (
            $paymentStatus === PaymentStatus::Paid
            && $currentStatus === OrderStatus::Pending->value
        ) {
            $updates['status_code'] = OrderStatus::Confirmed->value;
        }

        if (
            $paymentStatus === PaymentStatus::Failed
            && in_array($currentStatus, [OrderStatus::Pending->value, OrderStatus::Confirmed->value], true)
        ) {
            $updates['status_code'] = OrderStatus::Cancelled->value;
            $updates['cancel_reason'] = 'Payment failed via Midtrans ('.$transactionStatus.').';
        }

        $order->update($updates);

        return $order->fresh(['items']);
    }

    /**
     * Synchronize a single Midtrans order with gateway status.
     * This is safe to call on read paths (e.g. order detail polling) and throttled per order.
     */
    public function syncOrderFromGateway(Order $order, int $throttleSeconds = 3): Order
    {
        if (! $this->isConfigured()) {
            return $order;
        }

        $provider = strtolower((string) ($order->payment_provider ?? ''));
        if ($provider !== 'midtrans') {
            return $order;
        }

        $paymentStatus = (int) ($order->payment_status_code?->value ?? $order->payment_status_code);
        $orderStatus = (int) ($order->status_code?->value ?? $order->status_code);

        if ($paymentStatus !== PaymentStatus::Unpaid->value) {
            return $order;
        }

        if (! in_array($orderStatus, [OrderStatus::Pending->value, OrderStatus::Confirmed->value], true)) {
            return $order;
        }

        $cacheKey = 'midtrans:order-sync:'.$order->id;
        $ttl = max(1, $throttleSeconds);

        if (! Cache::add($cacheKey, now()->timestamp, now()->addSeconds($ttl))) {
            return $order->fresh(['items']) ?? $order;
        }

        try {
            $gatewayStatus = $this->fetchTransactionStatus((string) $order->order_number);

            return $this->applyNotificationToOrder($order, $gatewayStatus);
        } catch (RuntimeException|ValidationException|Throwable $exception) {
            report($exception);

            return $order->fresh(['items']) ?? $order;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function mapPaymentStatus(array $payload): PaymentStatus
    {
        $transactionStatus = strtolower((string) ($payload['transaction_status'] ?? ''));
        $fraudStatus = strtolower((string) ($payload['fraud_status'] ?? ''));

        return match ($transactionStatus) {
            'settlement' => PaymentStatus::Paid,
            'capture' => $fraudStatus === 'accept' || $fraudStatus === '' ? PaymentStatus::Paid : PaymentStatus::Unpaid,
            'pending' => PaymentStatus::Unpaid,
            'refund', 'partial_refund', 'chargeback', 'partial_chargeback' => PaymentStatus::Refunded,
            'deny', 'cancel', 'expire', 'failure' => PaymentStatus::Failed,
            default => PaymentStatus::Unpaid,
        };
    }

    /**
     * @return array{processed:int,updated:int,skipped:int,failed:int}
     */
    public function reconcilePendingOrders(int $limit = 50): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Midtrans is not configured.');
        }

        $summary = [
            'processed' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        $orders = Order::query()
            ->where('payment_provider', 'midtrans')
            ->where('payment_status_code', PaymentStatus::Unpaid->value)
            ->whereIn('status_code', [OrderStatus::Pending->value, OrderStatus::Confirmed->value])
            ->whereNotNull('order_number')
            ->orderBy('created_at')
            ->limit(max(1, $limit))
            ->get();

        foreach ($orders as $order) {
            $summary['processed']++;

            $beforeStatus = (int) ($order->status_code?->value ?? $order->status_code);
            $beforePayment = (int) ($order->payment_status_code?->value ?? $order->payment_status_code);

            try {
                $gatewayStatus = $this->fetchTransactionStatus((string) $order->order_number);
                $updatedOrder = $this->applyNotificationToOrder($order, $gatewayStatus);
            } catch (RuntimeException $exception) {
                report($exception);
                $summary['failed']++;

                continue;
            }

            $afterStatus = (int) ($updatedOrder->status_code?->value ?? $updatedOrder->status_code);
            $afterPayment = (int) ($updatedOrder->payment_status_code?->value ?? $updatedOrder->payment_status_code);

            if ($afterStatus !== $beforeStatus || $afterPayment !== $beforePayment) {
                $summary['updated']++;

                continue;
            }

            $summary['skipped']++;
        }

        return $summary;
    }

    protected function apiBaseUrl(): string
    {
        return config('midtrans.is_production', false)
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    protected function serverKey(): string
    {
        return (string) config('midtrans.server_key', '');
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    protected function buildItemDetails(Order $order): array
    {
        $items = [];

        foreach ($order->items as $item) {
            $items[] = [
                'id' => (string) $item->product_id,
                'price' => $this->toIntAmount($item->price),
                'quantity' => (int) $item->qty,
                'name' => (string) $item->product_name,
            ];
        }

        if ($this->toIntAmount($order->delivery_fee) > 0) {
            $items[] = [
                'id' => 'DELIVERY_FEE',
                'price' => $this->toIntAmount($order->delivery_fee),
                'quantity' => 1,
                'name' => 'Biaya Pengiriman',
            ];
        }

        if ($this->toIntAmount($order->discount_amount) > 0) {
            $items[] = [
                'id' => 'DISCOUNT',
                'price' => -$this->toIntAmount($order->discount_amount),
                'quantity' => 1,
                'name' => 'Diskon',
            ];
        }

        return $items;
    }

    protected function toIntAmount(mixed $amount): int
    {
        return (int) round((float) $amount, 0);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function extractPaymentChannel(array $payload): ?string
    {
        $channel = '';

        if (is_array($payload['va_numbers'] ?? null)) {
            $channel = (string) ($payload['va_numbers'][0]['bank'] ?? '');
        }

        if ($channel === '' && is_string($payload['permata_va_number'] ?? null)) {
            $channel = (string) ($payload['permata_va_number'] ?? '');
        }

        if ($channel === '') {
            $channel = (string) ($payload['store'] ?? '');
        }

        return $channel === '' ? null : $channel;
    }
}
