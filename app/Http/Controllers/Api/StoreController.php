<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCoverageRequest;
use App\Http\Resources\Api\StoreResource;
use App\Models\Store;
use App\Support\ApiResponse;
use App\Support\DistanceCalculator;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    public function show(): JsonResponse
    {
        $store = Store::query()->first();
        if (! $store) {
            return ApiResponse::error('Store is not available.', 404);
        }

        return ApiResponse::success(new StoreResource($store));
    }

    public function coverage(StoreCoverageRequest $request): JsonResponse
    {
        $store = Store::query()->first();
        if (! $store) {
            return ApiResponse::error('Store is not available.', 404);
        }

        $distance = DistanceCalculator::kilometers(
            (float) $store->latitude,
            (float) $store->longitude,
            (float) $request->input('latitude'),
            (float) $request->input('longitude'),
        );

        return ApiResponse::success([
            'distance_km' => $distance,
            'service_radius_km' => (float) $store->service_radius_km,
            'is_covered' => $distance <= (float) $store->service_radius_km,
        ]);
    }
}
