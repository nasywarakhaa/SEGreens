<?php

namespace App\Http\Controllers\Api;

use App\Enums\CartStatus;
use App\Enums\FulfillmentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutPreviewRequest;
use App\Http\Resources\Api\CartResource;
use App\Http\Resources\Api\StoreResource;
use App\Http\Resources\Api\UserAddressResource;
use App\Models\Cart;
use App\Models\Store;
use App\Models\UserAddress;
use App\Services\CheckoutService;
use App\Services\OrderScheduleService;
use App\Services\SystemSettingService;
use App\Support\ApiResponse;
use App\Support\DistanceCalculator;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function preview(
        CheckoutPreviewRequest $request,
        CheckoutService $checkoutService,
        SystemSettingService $settings,
        OrderScheduleService $orderScheduleService,
    ): JsonResponse {
        $user = $request->user();

        if ($settings->getBool('app', 'require_email_verification', true) && ! $user->hasVerifiedEmail()) {
            return ApiResponse::error('Email verification is required.', 403);
        }

        $store = Store::query()->first();
        if (! $store) {
            return ApiResponse::error('Store is not available.', 404);
        }

        $address = UserAddress::query()
            ->where('id', $request->input('address_id'))
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $address) {
            return ApiResponse::error('Address not found.', 404);
        }

        $cart = Cart::query()
            ->with('items.product')
            ->firstOrCreate([
                'user_id' => $user->id,
                'status_code' => CartStatus::Active->value,
            ]);

        if ($cart->items->isEmpty()) {
            return ApiResponse::error('Cart is empty.', 422);
        }

        $fulfillmentType = FulfillmentType::from($request->integer('fulfillment_type_code'));

        if ($fulfillmentType === FulfillmentType::Delivery) {
            $distance = DistanceCalculator::kilometers(
                (float) $store->latitude,
                (float) $store->longitude,
                (float) $address->latitude,
                (float) $address->longitude,
            );

            if ($distance > (float) $store->service_radius_km) {
                return ApiResponse::error('Address is outside service radius.', 422);
            }
        }

        foreach ($cart->items as $item) {
            $product = $item->product;
            if (! $product || ! $product->is_active || $product->stock < $item->qty) {
                return ApiResponse::error('One or more products are unavailable.', 422);
            }

            if ($item->qty < $product->min_order_qty) {
                return ApiResponse::error('One or more items are below minimum order quantity.', 422);
            }
        }

        $selectedSchedule = $orderScheduleService->resolve(
            scheduleDate: $request->input('schedule_date'),
            scheduleSlotCode: $request->filled('schedule_slot_code') ? $request->integer('schedule_slot_code') : null,
            scheduleAt: $request->input('schedule_at'),
        );
        $scheduleMetadata = $orderScheduleService->buildMetadata($selectedSchedule);

        $totals = $checkoutService->preview($cart, $store, $address, $fulfillmentType);
        $storePayload = (new StoreResource($store))->resolve($request);
        $storePayload['schedule'] = $scheduleMetadata;

        return ApiResponse::success([
            'store' => $storePayload,
            'address' => new UserAddressResource($address),
            'cart' => new CartResource($cart),
            'fulfillment_type_code' => $fulfillmentType->value,
            'schedule' => $scheduleMetadata,
            'totals' => $totals,
        ]);
    }
}
