<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = $this->resource->relationLoaded('items') ? $this->items : collect();

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'store_id' => $this->store_id,
            'user_address_id' => $this->user_address_id,
            'fulfillment_type_code' => $this->fulfillment_type_code?->value ?? $this->fulfillment_type_code,
            'status_code' => $this->status_code?->value ?? $this->status_code,
            'payment_status_code' => $this->payment_status_code?->value ?? $this->payment_status_code,
            'payment' => [
                'provider' => $this->payment_provider,
                'reference' => $this->payment_reference,
                'method' => $this->payment_method,
                'channel' => $this->payment_channel,
                'token' => $this->payment_token,
                'redirect_url' => $this->payment_redirect_url,
                'paid_at' => $this->paid_at,
            ],
            'note' => $this->note,
            'cancel_reason' => $this->cancel_reason,
            'schedule_at' => $this->schedule_at,
            'distance_km' => (float) $this->distance_km,
            'subtotal' => (float) $this->subtotal,
            'delivery_fee' => (float) $this->delivery_fee,
            'discount_amount' => (float) $this->discount_amount,
            'total_price' => (float) $this->total_price,
            'items' => OrderItemResource::collection($items),
            'created_at' => $this->created_at,
        ];
    }
}
