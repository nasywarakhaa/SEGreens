<?php

namespace App\Http\Controllers\Api;

use App\Enums\CartStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CartItemStoreRequest;
use App\Http\Requests\Api\CartItemUpdateRequest;
use App\Http\Resources\Api\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart((string) $request->user()->id);
        $cart->load('items.product');

        return ApiResponse::success(new CartResource($cart));
    }

    public function store(CartItemStoreRequest $request): JsonResponse
    {
        $product = Product::query()
            ->where('id', $request->input('product_id'))
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return ApiResponse::error('Product not found.', 404);
        }

        $qty = $request->integer('qty');
        if ($qty < $product->min_order_qty) {
            return ApiResponse::error('Quantity is below minimum order.', 422);
        }

        if ($product->stock < $qty) {
            return ApiResponse::error('Insufficient stock.', 422);
        }

        $cart = $this->getOrCreateCart((string) $request->user()->id);
        $item = $cart->items()->where('product_id', $product->id)->first();
        $newQty = $item ? $item->qty + $qty : $qty;

        if ($product->stock < $newQty) {
            return ApiResponse::error('Insufficient stock.', 422);
        }

        $cart->items()->updateOrCreate(
            ['product_id' => $product->id],
            ['qty' => $newQty, 'price' => $product->price],
        );

        $cart->load('items.product');

        return ApiResponse::success(new CartResource($cart), 'Item added.');
    }

    public function update(CartItemUpdateRequest $request, string $id): JsonResponse
    {
        $item = CartItem::query()
            ->where('id', $id)
            ->whereHas('cart', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('status_code', CartStatus::Active->value);
            })
            ->first();

        if (! $item) {
            return ApiResponse::error('Cart item not found.', 404);
        }

        $product = $item->product;
        if (! $product || ! $product->is_active) {
            return ApiResponse::error('Product is unavailable.', 422);
        }

        $qty = $request->integer('qty');
        if ($qty < $product->min_order_qty) {
            return ApiResponse::error('Quantity is below minimum order.', 422);
        }

        if ($product->stock < $qty) {
            return ApiResponse::error('Insufficient stock.', 422);
        }

        $item->update([
            'qty' => $qty,
            'price' => $product->price,
        ]);

        $cart = $item->cart->load('items.product');

        return ApiResponse::success(new CartResource($cart), 'Item updated.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $item = CartItem::query()
            ->where('id', $id)
            ->whereHas('cart', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('status_code', CartStatus::Active->value);
            })
            ->first();

        if (! $item) {
            return ApiResponse::error('Cart item not found.', 404);
        }

        $item->delete();

        $cart = $this->getOrCreateCart((string) $request->user()->id);
        $cart->load('items.product');

        return ApiResponse::success(new CartResource($cart), 'Item removed.');
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart((string) $request->user()->id);
        $cart->items()->delete();

        $cart->load('items.product');

        return ApiResponse::success(new CartResource($cart), 'Cart cleared.');
    }

    protected function getOrCreateCart(string $userId): Cart
    {
        return Cart::query()->firstOrCreate([
            'user_id' => $userId,
            'status_code' => CartStatus::Active->value,
        ]);
    }
}
