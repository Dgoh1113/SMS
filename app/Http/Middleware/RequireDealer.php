<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireDealer
{
    /**
     * Only dealers may access dealer routes (e.g. direct inquiry link).
     * Admin, manager, or unauthenticated users are sent to the login page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->session()->get('user_role');

        if ($role !== 'dealer') {
            $request->session()->put('url.intended', $request->fullUrl());
            return redirect()->route('login');
        }

        return $next($request);
    }
}
