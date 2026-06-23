<?php

namespace App\Models;

use App\Enums\CartStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasName, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'email_verified_at',
        'username',
        'phone_number',
        'password',
        'avatar',
        'fcm_token',
        'role_code',
        'status_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role_code' => UserRole::class,
            'status_code' => UserStatus::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role_code, [UserRole::Superuser, UserRole::Admin], true)
            && $this->status_code === UserStatus::Active;
    }

    public function getFilamentName(): string
    {
        return (string) ($this->full_name ?? $this->email ?? $this->username ?? '');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ?: null;
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    public function activeCart(): HasOne
    {
        return $this->hasOne(Cart::class)->where('status_code', CartStatus::Active->value);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
