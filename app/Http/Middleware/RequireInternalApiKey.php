<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireInternalApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = trim((string) config('services.internal_api.key', ''));

        if ($configuredKey === '') {
            return response()->json(['error' => 'This API endpoint is disabled.'], 403);
        }

        $providedKey = trim((string) $request->header('X-Internal-Api-Key', ''));

        if ($providedKey === '' || ! hash_equals($configuredKey, $providedKey)) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
