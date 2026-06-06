<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ResolvesConsoleRedirects
{
    protected function systemRoleToSessionRole(string $systemRole): string
    {
        return match (strtoupper(trim($systemRole))) {
            'ADMIN' => 'admin',
            'MANAGER' => 'manager',
            default => 'dealer',
        };
    }

    protected function dashboardPathForRole(Request $request, string $role): string
    {
        if ($role === 'admin' || $role === 'manager') {
            return '/admin/dashboard';
        }

        $intended = $request->session()->get('url.intended');
        if ($intended && str_starts_with(parse_url($intended, PHP_URL_PATH) ?: '', '/dealer/')) {
            $request->session()->forget('url.intended');

            return $intended;
        }

        return '/dealer/dashboard';
    }
}
