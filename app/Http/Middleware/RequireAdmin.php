<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->session()->get('user_role');
        if (!in_array($role, ['admin', 'manager'], true)) {
            return redirect('/login');
        }
        return $next($request);
    }
}
