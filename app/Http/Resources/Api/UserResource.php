<?php

namespace App\Http\Resources\Api;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'full_name' => $this->full_name,
            'email' => $this->email,
            'username' => $this->username,
            'email_verified' => ! is_null($this->email_verified_at),
            'phone_number' => $this->phone_number,
            'avatar' => MediaUrl::from($this->avatar),
            'fcm_token' => $this->fcm_token,
            'role_code' => $this->role_code?->value ?? $this->role_code,
            'status_code' => $this->status_code?->value ?? $this->status_code,
            'created_at' => $this->created_at,
        ];
    }
}
