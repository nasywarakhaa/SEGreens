<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'user_id' => $this->user_id,
            'status_code' => $this->status_code?->value ?? $this->status_code,
            'checked_out_at' => $this->checked_out_at,
            'items' => CartItemResource::collection($items),
        ];
    }
}
