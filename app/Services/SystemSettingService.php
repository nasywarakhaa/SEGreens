<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    /**
     * Retrieve a setting value by group and key.
     */
    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        return $settings[$group][$key] ?? $default;
    }

    /**
     * Retrieve a boolean setting.
     */
    public function getBool(string $group, string $key, bool $default = false): bool
    {
        return filter_var($this->get($group, $key, $default), FILTER_VALIDATE_BOOL);
    }

    /**
     * Retrieve all settings grouped by group_name.
     */
    public function all(): array
    {
        return Cache::remember('system_settings.cache', now()->addMinutes(5), function (): array {
            return SystemSetting::query()
                ->where('is_active', true)
                ->get()
                ->groupBy('group_name')
                ->map(function ($items) {
                    return $items->mapWithKeys(fn ($item) => [$item->key_name => $item->value])->toArray();
                })
                ->toArray();
        });
    }

    /**
     * Clear settings cache.
     */
    public function clearCache(): void
    {
        Cache::forget('system_settings.cache');
    }
}
