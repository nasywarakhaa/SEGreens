<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    use HasFactory, HasUuids;

    private const ENCRYPTED_PREFIX = 'enc::';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'group_name',
        'key_name',
        'label',
        'value',
        'type',
        'is_encrypted',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $setting): void {
            $value = $setting->getAttributeFromArray('value');

            if ($value === null || $value === '') {
                return;
            }

            $rawValue = (string) $value;

            if ($setting->is_encrypted) {
                if (str_starts_with($rawValue, self::ENCRYPTED_PREFIX)) {
                    return;
                }

                $setting->attributes['value'] = self::ENCRYPTED_PREFIX.Crypt::encryptString($rawValue);

                return;
            }

            if (! str_starts_with($rawValue, self::ENCRYPTED_PREFIX)) {
                return;
            }

            try {
                $setting->attributes['value'] = Crypt::decryptString(substr($rawValue, strlen(self::ENCRYPTED_PREFIX)));
            } catch (DecryptException) {
                // Keep raw value when payload is not decryptable.
            }
        });
    }

    public function getValueAttribute(?string $value): ?string
    {
        if ($value === null || ! $this->is_encrypted) {
            return $value;
        }

        if (! str_starts_with($value, self::ENCRYPTED_PREFIX)) {
            return $value;
        }

        try {
            return Crypt::decryptString(substr($value, strlen(self::ENCRYPTED_PREFIX)));
        } catch (DecryptException) {
            return $value;
        }
    }
}
