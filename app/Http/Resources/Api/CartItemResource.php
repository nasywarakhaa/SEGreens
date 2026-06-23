<?php

namespace App\Http\Resources\Api;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->resource->relationLoaded('product') ? $this->product : null;

        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'product_id' => $this->product_id,
            'qty' => (int) $this->qty,
            'price' => (float) $this->price,
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'image' => MediaUrl::from($product->image),
                'stock' => (int) $product->stock,
                'weight' => (float) $product->weight,
                'unit' => $product->unit,
            ] : null,
        ];
    }
}
