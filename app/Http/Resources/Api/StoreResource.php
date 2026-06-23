<?php

namespace App\Http\Resources\Api;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'service_radius_km' => (float) $this->service_radius_km,
            'base_delivery_fee' => (float) $this->base_delivery_fee,
            'phone_number' => $this->phone_number,
            'logo' => MediaUrl::from($this->logo),
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
        ];
    }
}
