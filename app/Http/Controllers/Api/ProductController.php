<?php

namespace App\Http\Controllers\Api;

use App\Enums\CartStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductIndexRequest;
use App\Http\Resources\Api\ProductResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 20);
        $page = $request->integer('page', 1);

        $query = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0);

        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->input('category_id'));
        } elseif ($request->filled('category_slug')) {
            $query->whereHas('category', function ($categoryQuery) use ($request): void {
                $categoryQuery->where('slug', $request->input('category_slug'));
            });
        }

        $keyword = $request->input('search', $request->input('q'));
        if (is_string($keyword) && $keyword !== '') {
            $query->where('name', 'like', '%'.$keyword.'%');
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float) $request->input('price_min'));
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float) $request->input('price_max'));
        }

        match ($request->input('sort', 'default')) {
            'random' => $query->inRandomOrder(),
            'lowest', 'cheapest' => $query->orderBy('price')->orderBy('name'),
            'highest', 'expensive' => $query->orderByDesc('price')->orderBy('name'),
            'popular', 'best_selling' => $query->orderByDesc('sell_count')->orderBy('name'),
            'least_selling' => $query->orderBy('sell_count')->orderBy('name'),
            default => $query->orderBy('sort_order')->orderBy('name'),
        };

        $products = $query->paginate($perPage, ['*'], 'page', $page);

        return ApiResponse::paginated($products, ProductResource::collection($products));
    }

    public function search(ProductIndexRequest $request): JsonResponse
    {
        return $this->index($request);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $product = Product::query()
            ->where('id', $id)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return ApiResponse::error('Product not found.', 404);
        }

        $cartItem = null;
        $user = $request->user('sanctum');
        if ($user instanceof User) {
            $cartItem = CartItem::query()
                ->where('product_id', $product->id)
                ->whereHas('cart', function ($query) use ($user): void {
                    $query
                        ->where('user_id', $user->id)
                        ->where('status_code', CartStatus::Active->value);
                })
                ->first(['id', 'qty']);
        }

        $payload = (new ProductResource($product))->resolve($request);
        $payload['cart'] = [
            'item_id' => $cartItem?->id,
            'qty' => (int) ($cartItem?->qty ?? 0),
        ];

        return ApiResponse::success($payload);
    }
}
