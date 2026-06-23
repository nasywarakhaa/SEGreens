<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserAddressRequest;
use App\Http\Resources\Api\UserAddressResource;
use App\Models\UserAddress;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 20);

        $addresses = $request->user()
            ->addresses()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return ApiResponse::paginated($addresses, UserAddressResource::collection($addresses));
    }

    public function store(UserAddressRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['user_id'] = $user->id;
        // Always store newly created address as active to avoid legacy DB default mismatch.
        $data['is_active'] = true;

        if (! $user->addresses()->exists()) {
            $data['is_default'] = true;
        }

        $address = UserAddress::query()->create($data);

        if (! empty($data['is_default'])) {
            UserAddress::query()
                ->where('user_id', $user->id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        return ApiResponse::success(new UserAddressResource($address), 'Address created.', 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $address = UserAddress::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $address) {
            return ApiResponse::error('Address not found.', 404);
        }

        return ApiResponse::success(new UserAddressResource($address));
    }

    public function setDefault(Request $request, string $id): JsonResponse
    {
        $address = UserAddress::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $address) {
            return ApiResponse::error('Address not found.', 404);
        }

        DB::transaction(function () use ($request, $address): void {
            UserAddress::query()
                ->where('user_id', $request->user()->id)
                ->where('is_active', true)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);

            $address->update(['is_default' => true]);
        });

        return ApiResponse::success(new UserAddressResource($address->fresh()), 'Default address updated.');
    }

    public function update(UserAddressRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        $address = UserAddress::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $address) {
            return ApiResponse::error('Address not found.', 404);
        }

        $data = $request->validated();
        $address->update($data);

        if (! empty($data['is_default'])) {
            UserAddress::query()
                ->where('user_id', $user->id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        return ApiResponse::success(new UserAddressResource($address->fresh()), 'Address updated.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $address = UserAddress::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        if (! $address) {
            return ApiResponse::error('Address not found.', 404);
        }

        $wasDefault = (bool) $address->is_default;
        $address->update(['is_active' => false, 'is_default' => false]);

        if ($wasDefault) {
            $replacement = UserAddress::query()
                ->where('user_id', $request->user()->id)
                ->where('is_active', true)
                ->where('id', '!=', $address->id)
                ->orderBy('id')
                ->first();

            if ($replacement) {
                $replacement->update(['is_default' => true]);
            }
        }

        return ApiResponse::success(null, 'Address deactivated.');
    }
}
