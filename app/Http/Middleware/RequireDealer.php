<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireDealer
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('user_role') !== 'dealer') {
            return redirect('/login');
        }
        return $next($request);
    }
}
