<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Services\MidtransService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class MidtransPaymentController extends Controller
{
    public function create(Request $request, MidtransService $midtransService, string $id): JsonResponse
    {
        if (! $midtransService->isConfigured()) {
            return ApiResponse::error('Midtrans is not configured.', 503);
        }

        $order = $this->findOwnedOrder($request, $id);
        if (! $order) {
            return ApiResponse::error('Order not found.', 404);
        }

        $order = $midtransService->syncOrderFromGateway($order);

        $orderStatus = (int) ($order->status_code?->value ?? $order->status_code);
        if ($orderStatus === OrderStatus::Cancelled->value) {
            return ApiResponse::error('Cancelled order cannot be paid.', 422);
        }

        if (($order->payment_status_code?->value ?? (int) $order->payment_status_code) === PaymentStatus::Paid->value) {
            return ApiResponse::success([
                'order' => new OrderResource($order->loadMissing('items')),
                'payment' => [
                    'provider' => 'midtrans',
                    'merchant_id' => $midtransService->merchantId(),
                    'client_key' => $midtransService->clientKey(),
                    'token' => $order->payment_token,
                    'redirect_url' => $order->payment_redirect_url,
                ],
            ], 'Order already paid.');
        }

        if (
            ($order->payment_status_code?->value ?? (int) $order->payment_status_code) === PaymentStatus::Unpaid->value
            && $order->payment_token
            && $order->payment_redirect_url
        ) {
            return ApiResponse::success([
                'order' => new OrderResource($order->loadMissing('items')),
                'payment' => [
                    'provider' => 'midtrans',
                    'merchant_id' => $midtransService->merchantId(),
                    'client_key' => $midtransService->clientKey(),
                    'token' => $order->payment_token,
                    'redirect_url' => $order->payment_redirect_url,
                ],
            ], 'Existing Midtrans payment token returned.');
        }

        try {
            $snap = $midtransService->createSnapTransaction($order);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        $order->update([
            'payment_provider' => 'midtrans',
            'payment_token' => $snap['token'],
            'payment_redirect_url' => $snap['redirect_url'],
        ]);

        return ApiResponse::success([
            'order' => new OrderResource($order->fresh('items')),
            'payment' => [
                'provider' => 'midtrans',
                'merchant_id' => $midtransService->merchantId(),
                'client_key' => $midtransService->clientKey(),
                'token' => $snap['token'],
                'redirect_url' => $snap['redirect_url'],
            ],
        ], 'Midtrans payment token created.');
    }

    public function status(Request $request, MidtransService $midtransService, string $id): JsonResponse
    {
        if (! $midtransService->isConfigured()) {
            return ApiResponse::error('Midtrans is not configured.', 503);
        }

        $order = $this->findOwnedOrder($request, $id);
        if (! $order) {
            return ApiResponse::error('Order not found.', 404);
        }

        try {
            $gatewayStatus = $midtransService->fetchTransactionStatus((string) $order->order_number);
            $order = $midtransService->applyNotificationToOrder($order, $gatewayStatus);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        } catch (ValidationException $exception) {
            return ApiResponse::error('Unable to update order payment status.', 422, $exception->errors());
        }

        return ApiResponse::success([
            'order' => new OrderResource($order->fresh('items')),
            'gateway' => [
                'transaction_status' => $gatewayStatus['transaction_status'] ?? null,
                'fraud_status' => $gatewayStatus['fraud_status'] ?? null,
                'payment_type' => $gatewayStatus['payment_type'] ?? null,
                'transaction_id' => $gatewayStatus['transaction_id'] ?? null,
                'gross_amount' => $gatewayStatus['gross_amount'] ?? null,
                'transaction_time' => $gatewayStatus['transaction_time'] ?? null,
            ],
        ]);
    }

    public function notification(Request $request, MidtransService $midtransService): JsonResponse
    {
        if (! $midtransService->isConfigured()) {
            return ApiResponse::error('Midtrans is not configured.', 503);
        }

        /** @var array<string, mixed> $payload */
        $payload = $request->all();
        if (! $midtransService->verifyNotificationSignature($payload)) {
            return ApiResponse::error('Invalid Midtrans signature.', 403);
        }

        $orderNumber = (string) ($payload['order_id'] ?? '');
        if ($orderNumber === '') {
            return ApiResponse::error('Invalid Midtrans payload.', 422);
        }

        $order = Order::query()->where('order_number', $orderNumber)->first();
        if (! $order) {
            return ApiResponse::success(null, 'Order not found. Notification ignored.');
        }

        try {
            $order = $midtransService->applyNotificationToOrder($order, $payload);
        } catch (ValidationException $exception) {
            return ApiResponse::success([
                'order' => new OrderResource($order->fresh('items')),
            ], 'Notification ignored due to order transition guard.');
        } catch (Throwable $exception) {
            report($exception);

            return ApiResponse::error('Unable to process Midtrans notification.', 500);
        }

        return ApiResponse::success([
            'order' => new OrderResource($order),
        ], 'Midtrans notification processed.');
    }

    protected function findOwnedOrder(Request $request, string $id): ?Order
    {
        return Order::query()
            ->where('id', $id)
            ->where('user_id', (string) $request->user()->id)
            ->with('items')
            ->first();
    }
}
