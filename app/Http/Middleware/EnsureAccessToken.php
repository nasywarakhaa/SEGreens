<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccessToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if (! $token) {
            return ApiResponse::error('Unauthorized.', 401);
        }

        if (! $token->can('access')) {
            return ApiResponse::error('Invalid access token.', 401);
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();

            return ApiResponse::error('Access token expired.', 401);
        }

        return $next($request);
    }
}
