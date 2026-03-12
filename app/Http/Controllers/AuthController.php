<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        // After sign-in success we stay on login page to show register passkey; only then go to dashboard.
        if ($request->session()->has('user_id') && $request->session()->has('show_register_passkey')) {
            $role = $request->session()->get('user_role');
            $dashboardUrl = ($role === 'admin' || $role === 'manager') ? '/admin/dashboard' : '/dealer/dashboard';
            return view('auth.login', ['show_register_passkey' => true, 'dashboard_url' => $dashboardUrl]);
        }
        if ($request->session()->has('user_role')) {
            $role = $request->session()->get('user_role');
            if ($role === 'admin' || $role === 'manager') {
                return redirect('/admin/dashboard');
            }
            return redirect('/dealer/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        // Database login
        $row = DB::selectOne(
            'SELECT "USERID", "PASSWORDHASH", "SYSTEMROLE", "ISACTIVE", "ALIAS" FROM "USERS" WHERE "EMAIL" = ?',
            [$email]
        );

        if (!$row) {
            return back()->withInput($request->only('email'))->with('error', 'Invalid email or password.');
        }
        if (!$row->ISACTIVE) {
            return back()->withInput($request->only('email'))->with('error', 'Account is deactivated.');
        }

        $stored = (string) ($row->PASSWORDHASH ?? '');
        $looksHashed = str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$argon2');
        $ok = $looksHashed ? Hash::check($password, $stored) : hash_equals($stored, $password);

        if (!$ok) {
            return back()->withInput($request->only('email'))->with('error', 'Invalid email or password.');
        }

        // If legacy plaintext was stored, upgrade it to a real hash on successful login.
        if (!$looksHashed) {
            DB::update(
                'UPDATE "USERS" SET "PASSWORDHASH" = ? WHERE "USERID" = ?',
                [Hash::make($password), $row->USERID]
            );
        }

        DB::update('UPDATE "USERS" SET "LASTLOGIN" = CURRENT_TIMESTAMP WHERE "USERID" = ?', [$row->USERID]);

        $systemRole = strtoupper(trim((string) ($row->SYSTEMROLE ?? '')));
        $role = match ($systemRole) {
            'ADMIN' => 'admin',
            'MANAGER' => 'manager',
            default => 'dealer',
        };
        $request->session()->put('user_id', $row->USERID);
        $request->session()->put('user_email', $email);
        $request->session()->put('user_alias', $row->ALIAS ?? '');
        $request->session()->put('user_role', $role);

        // Stay on login page and show register passkey; redirect to dashboard only after they register or skip.
        $request->session()->flash('show_register_passkey', true);
        return redirect()->route('login');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
