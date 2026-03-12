<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Enforce strict idle timeout (SESSION_LIFETIME) even when using file sessions
        // because file-session garbage collection is probabilistic.
        try {
            $lifetimeMinutes = (int) config('session.lifetime', 120);
            $maxIdleSeconds = max(1, $lifetimeMinutes) * 60;
            $last = (int) ($request->session()->get('last_activity_ts') ?? 0);
            $now = time();
            if ($last > 0 && ($now - $last) > $maxIdleSeconds) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect('/login');
            }
            $request->session()->put('last_activity_ts', $now);
        } catch (\Throwable $e) {
            // If session isn't available, fall through to auth check
        }

        if (!$request->session()->has('user_role')) {
            return redirect('/login');
        }
        return $next($request);
    }
}
