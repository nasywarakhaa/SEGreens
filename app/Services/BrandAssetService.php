<?php

namespace App\Services;

use App\Models\Store;
use App\Support\MediaUrl;
use Illuminate\Support\Facades\Cache;
use Throwable;

class BrandAssetService
{
    private const CACHE_KEY = 'store_branding.cache';

    public function getBrandName(): string
    {
        $name = trim((string) ($this->getBrandingData()['name'] ?? ''));

        return $name !== '' ? $name : $this->getDefaultBrandName();
    }

    public function getLogoUrl(): string
    {
        $logoUrl = trim((string) ($this->getBrandingData()['logo_url'] ?? ''));

        return $logoUrl !== '' ? $logoUrl : $this->getDefaultLogoUrl();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{name: string, logo_url: string}
     */
    private function getBrandingData(): array
    {
        /** @var array{name: string, logo_url: string} $branding */
        $branding = Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function (): array {
            try {
                $store = Store::query()
                    ->select(['name', 'logo'])
                    ->first();
            } catch (Throwable) {
                return [
                    'name' => '',
                    'logo_url' => '',
                ];
            }

            return [
                'name' => trim((string) ($store?->name ?? '')),
                'logo_url' => trim((string) (MediaUrl::from($store?->logo) ?? '')),
            ];
        });

        return $branding;
    }

    private function getDefaultBrandName(): string
    {
        $name = trim((string) (config('mail.from.name') ?: config('app.name', '')));

        return ($name === '' || strcasecmp($name, 'Laravel') === 0) ? 'SEGreens' : $name;
    }

    private function getDefaultLogoUrl(): string
    {
        $configured = trim((string) config('mail.brand_logo_url', ''));
        if ($configured !== '') {
            return $configured;
        }

        $appUrl = rtrim((string) config('app.url', 'http://localhost'), '/');

        return $appUrl.'/ic_segreens.png';
    }
}
