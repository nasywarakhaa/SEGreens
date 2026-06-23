<?php

namespace App\Http\Resources\Api;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'product_image' => MediaUrl::from($this->product_image),
            'product_weight' => (float) $this->product_weight,
            'product_unit' => $this->product_unit,
            'price' => (float) $this->price,
            'qty' => (int) $this->qty,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}
