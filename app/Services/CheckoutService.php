<?php

namespace App\Services;

use App\Enums\CartStatus;
use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\UserAddress;
use App\Support\DistanceCalculator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(private readonly DatabaseManager $db) {}

    public function preview(Cart $cart, Store $store, UserAddress $address, FulfillmentType $fulfillmentType): array
    {
        $subtotal = $cart->items->sum(fn ($item) => (float) $item->price * $item->qty);
        $distanceKm = $fulfillmentType === FulfillmentType::Delivery
            ? DistanceCalculator::kilometers(
                (float) $store->latitude,
                (float) $store->longitude,
                (float) $address->latitude,
                (float) $address->longitude,
            )
            : 0.0;

        $deliveryFee = $fulfillmentType === FulfillmentType::Delivery
            ? (float) $store->base_delivery_fee
            : 0.0;

        $discount = 0.0;
        $total = $subtotal + $deliveryFee - $discount;

        return [
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount_amount' => $discount,
            'total_price' => $total,
            'distance_km' => $distanceKm,
        ];
    }

    public function createOrder(Cart $cart, Store $store, UserAddress $address, FulfillmentType $fulfillmentType, ?string $note = null, ?string $scheduleAt = null): Order
    {
        $this->assertCartHasItems($cart);

        return $this->db->transaction(function () use ($cart, $store, $address, $fulfillmentType, $note, $scheduleAt) {
            $cart->loadMissing('items.product');

            foreach ($cart->items as $item) {
                /** @var Product $product */
                $product = $item->product;
                if (! $product || ! $product->is_active || $product->stock < $item->qty) {
                    throw new \RuntimeException('One or more products are unavailable.');
                }
            }

            $totals = $this->preview($cart, $store, $address, $fulfillmentType);

            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $cart->user_id,
                'store_id' => $store->id,
                'user_address_id' => $address->id,
                'fulfillment_type_code' => $fulfillmentType->value,
                'status_code' => OrderStatus::Pending->value,
                'payment_status_code' => PaymentStatus::Unpaid->value,
                'note' => $note,
                'schedule_at' => $scheduleAt,
                'distance_km' => $totals['distance_km'],
                'subtotal' => $totals['subtotal'],
                'delivery_fee' => $totals['delivery_fee'],
                'discount_amount' => $totals['discount_amount'],
                'total_price' => $totals['total_price'],
            ]);

            foreach ($cart->items as $item) {
                /** @var Product $product */
                $product = $item->product;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->image,
                    'product_weight' => $product->weight,
                    'product_unit' => $product->unit,
                    'price' => $item->price,
                    'qty' => $item->qty,
                    'subtotal' => (float) $item->price * $item->qty,
                ]);

                $product->decrement('stock', $item->qty);
            }

            $cart->update([
                'status_code' => CartStatus::CheckedOut->value,
                'checked_out_at' => now(),
            ]);

            return $order->load('items');
        });
    }

    protected function assertCartHasItems(Cart $cart): void
    {
        if ($cart->items()->count() === 0) {
            throw new \RuntimeException('Cart is empty.');
        }
    }

    protected function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
