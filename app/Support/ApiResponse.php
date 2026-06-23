<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;

class ApiResponse
{
    /**
     * Build a standardized success response.
     */
    public static function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        $normalized = self::normalizeResource($data);

        return response()->json([
            'message' => $message,
            'data' => $normalized,
        ], $status);
    }

    /**
     * Build a standardized paginated response.
     */
    public static function paginated(LengthAwarePaginator $paginator, JsonResource $resource): JsonResponse
    {
        $data = self::normalizeResource($resource);

        return response()->json([
            'message' => 'OK',
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Build an error response with optional validation details.
     */
    public static function error(string $message, int $status = 400, array|MessageBag|null $errors = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors instanceof MessageBag ? $errors->toArray() : Arr::wrap($errors ?? []),
        ], $status);
    }

    protected static function normalizeResource(mixed $data): mixed
    {
        if ($data instanceof JsonResource) {
            $resolved = $data->resolve();
            if (is_array($resolved) && array_key_exists('data', $resolved)) {
                return $resolved['data'];
            }

            return $resolved;
        }

        return $data;
    }
}
