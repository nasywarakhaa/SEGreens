<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnsureIdempotencyKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $idempotencyKey = $this->extractIdempotencyKey($request);
        if ($idempotencyKey === '') {
            return $next($request);
        }

        $cacheKey = $this->cacheKey($request, $idempotencyKey);
        $payloadHash = hash('sha256', (string) $request->getContent());

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            if (($cached['payload_hash'] ?? '') !== $payloadHash) {
                return ApiResponse::error('Idempotency-Key is already used for a different payload.', 422);
            }

            return $this->buildCachedResponse($cached);
        }

        $ttlSeconds = (int) config('api.idempotency.ttl_seconds', 600);
        $lockKey = $cacheKey.':lock';
        $acquired = Cache::add($lockKey, $payloadHash, now()->addSeconds(max(5, $ttlSeconds)));
        if (! $acquired) {
            return ApiResponse::error('Request with the same Idempotency-Key is still being processed.', 409);
        }

        try {
            $response = $next($request);
            $this->storeResponse($cacheKey, $payloadHash, $response, $ttlSeconds);

            return $response;
        } finally {
            Cache::forget($lockKey);
        }
    }

    /**
     * @param  array<string, mixed>  $cached
     */
    protected function buildCachedResponse(array $cached): Response
    {
        $response = response(
            (string) ($cached['body'] ?? ''),
            (int) ($cached['status'] ?? 200),
        );

        $headers = $cached['headers'] ?? [];
        if (! is_array($headers)) {
            return $response;
        }

        foreach ($headers as $name => $values) {
            if (! is_string($name)) {
                continue;
            }

            if (! is_array($values)) {
                continue;
            }

            $response->headers->set($name, $values);
        }

        $response->headers->set('X-Idempotent-Replay', 'true');

        return $response;
    }

    protected function storeResponse(string $cacheKey, string $payloadHash, Response $response, int $ttlSeconds): void
    {
        if ($response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            return;
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode >= 500) {
            return;
        }

        $body = $response->getContent();
        if (! is_string($body)) {
            return;
        }

        $maxBodyBytes = (int) config('api.idempotency.max_body_bytes', 65535);
        if ($maxBodyBytes > 0 && strlen($body) > $maxBodyBytes) {
            return;
        }

        Cache::put($cacheKey, [
            'payload_hash' => $payloadHash,
            'status' => $statusCode,
            'headers' => $response->headers->allPreserveCaseWithoutCookies(),
            'body' => $body,
        ], now()->addSeconds(max(5, $ttlSeconds)));
    }

    protected function extractIdempotencyKey(Request $request): string
    {
        $header = trim((string) $request->header('Idempotency-Key', ''));
        if ($header !== '') {
            return $header;
        }

        return trim((string) $request->header('X-Idempotency-Key', ''));
    }

    protected function cacheKey(Request $request, string $idempotencyKey): string
    {
        $userScope = (string) ($request->user()?->id ?? 'guest:'.$request->ip());
        $routeScope = (string) $request->route()?->uri();

        return 'idempotency:'.sha1($userScope.'|'.$request->method().'|'.$routeScope.'|'.$idempotencyKey);
    }
}
