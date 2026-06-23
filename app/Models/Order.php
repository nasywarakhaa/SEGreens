<?php

namespace App\Models;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Jobs\SendOrderStatusNotifications;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'store_id',
        'user_address_id',
        'fulfillment_type_code',
        'status_code',
        'payment_status_code',
        'payment_provider',
        'payment_reference',
        'payment_method',
        'payment_channel',
        'payment_token',
        'payment_redirect_url',
        'paid_at',
        'note',
        'cancel_reason',
        'schedule_at',
        'distance_km',
        'subtotal',
        'delivery_fee',
        'discount_amount',
        'total_price',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fulfillment_type_code' => FulfillmentType::class,
            'status_code' => OrderStatus::class,
            'payment_status_code' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'schedule_at' => 'datetime',
            'distance_km' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (self $order): void {
            $originalStatus = (int) $order->getRawOriginal('status_code');

            if (in_array($originalStatus, OrderStatus::terminalValues(), true) && $order->isDirty()) {
                throw ValidationException::withMessages([
                    'status_code' => ['Finalized order cannot be changed.'],
                ]);
            }

            if (! $order->isDirty('status_code')) {
                return;
            }

            $nextStatus = (int) ($order->status_code?->value ?? $order->status_code);
            if (OrderStatus::canTransition($originalStatus, $nextStatus)) {
                return;
            }

            throw ValidationException::withMessages([
                'status_code' => ['Invalid order status transition.'],
            ]);
        });

        static::updated(function (self $order): void {
            if (! $order->wasChanged('status_code')) {
                return;
            }

            $previousStatus = (int) $order->getRawOriginal('status_code');
            $currentStatus = (int) ($order->status_code?->value ?? $order->status_code);

            SendOrderStatusNotifications::dispatch(
                orderId: (string) $order->id,
                fromStatus: $previousStatus,
                toStatus: $currentStatus,
            )->afterCommit();

            if ($previousStatus === OrderStatus::Completed->value || $currentStatus !== OrderStatus::Completed->value) {
                return;
            }

            $order->loadMissing('items');

            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                Product::query()
                    ->whereKey($item->product_id)
                    ->increment('sell_count', (int) $item->qty);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function userAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
