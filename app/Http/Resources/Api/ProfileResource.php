<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $addresses = $this->relationLoaded('addresses') ? $this->addresses : collect();
        $defaultAddress = $addresses->firstWhere('is_default', true);

        return array_merge((new UserResource($this->resource))->toArray($request), [
            'default_address' => $defaultAddress ? new UserAddressResource($defaultAddress) : null,
            'addresses' => UserAddressResource::collection($addresses),
        ]);
    }
}
