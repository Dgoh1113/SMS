<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('user_role')) {
            return redirect('/login');
        }
        return $next($request);
    }
}
