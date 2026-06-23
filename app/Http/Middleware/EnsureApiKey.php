<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('api.mobile_key');

        if ($configuredKey === '') {
            return ApiResponse::error('API key is not configured.', 500);
        }

        $providedKey = $this->extractApiKey($request);

        if ($providedKey === null || ! hash_equals($configuredKey, $providedKey)) {
            return ApiResponse::error('Invalid API key.', 401);
        }

        return $next($request);
    }

    private function extractApiKey(Request $request): ?string
    {
        $headerKey = $request->header('X-API-KEY');
        if (is_string($headerKey) && $headerKey !== '') {
            return $headerKey;
        }

        $queryKey = $request->query('api_key');
        if (is_string($queryKey) && $queryKey !== '') {
            return $queryKey;
        }

        return null;
    }
}
