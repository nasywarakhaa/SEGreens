<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GeocodeReverseRequest;
use App\Http\Requests\Api\GeocodeSearchRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeocodingController extends Controller
{
    public function search(GeocodeSearchRequest $request): JsonResponse
    {
        $query = $request->input('q');
        $cacheKey = 'geocode.search.'.md5($query);

        $payload = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($query) {
            $response = $this->httpClient()->get($this->baseUrl().'/search', [
                'format' => 'jsonv2',
                'q' => $query,
                'limit' => 5,
            ]);

            if (! $response->ok()) {
                return null;
            }

            $items = collect($response->json())->map(function (array $item) {
                return [
                    'display_name' => $item['display_name'] ?? null,
                    'latitude' => isset($item['lat']) ? (float) $item['lat'] : null,
                    'longitude' => isset($item['lon']) ? (float) $item['lon'] : null,
                ];
            })->values()->all();

            return $items;
        });

        if ($payload === null) {
            return ApiResponse::error('Geocoding failed.', 502);
        }

        return ApiResponse::success($payload);
    }

    public function reverse(GeocodeReverseRequest $request): JsonResponse
    {
        $lat = (float) $request->input('latitude');
        $lng = (float) $request->input('longitude');
        $cacheKey = 'geocode.reverse.'.md5($lat.','.$lng);

        $payload = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($lat, $lng) {
            $response = $this->httpClient()->get($this->baseUrl().'/reverse', [
                'format' => 'jsonv2',
                'lat' => $lat,
                'lon' => $lng,
            ]);

            if (! $response->ok()) {
                return null;
            }

            $data = $response->json();

            return [
                'display_name' => $data['display_name'] ?? null,
                'latitude' => $lat,
                'longitude' => $lng,
            ];
        });

        if ($payload === null) {
            return ApiResponse::error('Reverse geocoding failed.', 502);
        }

        return ApiResponse::success($payload);
    }

    protected function httpClient()
    {
        return Http::timeout(8)->withHeaders([
            'User-Agent' => config('services.nominatim.user_agent', 'SEGreens/1.0'),
        ]);
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.nominatim.base_url', 'https://nominatim.openstreetmap.org'), '/');
    }
}
