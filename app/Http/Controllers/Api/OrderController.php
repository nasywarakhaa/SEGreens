<?php

namespace App\Http\Controllers\Api;

use App\Enums\CartStatus;
use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderCancelRequest;
use App\Http\Requests\Api\OrderStoreRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Store;
use App\Models\UserAddress;
use App\Services\CheckoutService;
use App\Services\MidtransService;
use App\Services\OrderScheduleService;
use App\Services\SystemSettingService;
use App\Support\ApiResponse;
use App\Support\DistanceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status_code' => ['sometimes', 'integer', 'in:1,2,3,4,5,6'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = Order::query()
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if (array_key_exists('status_code', $validated)) {
            $query->where('status_code', (int) $validated['status_code']);
        }

        $orders = $query->paginate($perPage, ['*'], 'page', $page);

        return ApiResponse::paginated($orders, OrderResource::collection($orders));
    }

    public function store(
        OrderStoreRequest $request,
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

        try {
            $order = $checkoutService->createOrder(
                $cart,
                $store,
                $address,
                $fulfillmentType,
                $request->input('note'),
                $selectedSchedule['schedule_at'],
            );
        } catch (\RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        return ApiResponse::success(new OrderResource($order), 'Order created.', 201);
    }

    public function show(Request $request, MidtransService $midtransService, string $id): JsonResponse
    {
        $order = Order::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->first();

        if (! $order) {
            return ApiResponse::error('Order not found.', 404);
        }

        $order = $midtransService->syncOrderFromGateway($order);

        return ApiResponse::success(new OrderResource($order));
    }

    public function cancel(OrderCancelRequest $request, string $id): JsonResponse
    {
        $order = Order::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('items.product')
            ->first();

        if (! $order) {
            return ApiResponse::error('Order not found.', 404);
        }

        $statusCode = $order->status_code?->value ?? (int) $order->status_code;
        if (! in_array($statusCode, [OrderStatus::Pending->value, OrderStatus::Confirmed->value], true)) {
            return ApiResponse::error('Order cannot be cancelled.', 422);
        }

        try {
            $order->update([
                'status_code' => OrderStatus::Cancelled->value,
                'cancel_reason' => $request->input('cancel_reason'),
            ]);
        } catch (ValidationException $exception) {
            return ApiResponse::error('Order cannot be cancelled.', 422, $exception->errors());
        }

        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->qty);
            }
        }

        return ApiResponse::success(new OrderResource($order->fresh('items')), 'Order cancelled.');
    }
}
