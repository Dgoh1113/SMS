<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('user_role')) {
            $role = $request->session()->get('user_role');
            return $role === 'admin' ? redirect('/admin/dashboard') : redirect('/dealer/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        // Demo fallback
        $u = strtoupper(trim($email));
        $p = trim($password);
        if ($u === 'ADMIN' && $p === 'ADMIN') {
            $request->session()->put('user_id', 0);
            $request->session()->put('user_email', 'admin@demo');
            $request->session()->put('user_role', 'admin');
            return redirect('/admin/dashboard');
        }
        if ($u === 'DEALER' && $p === 'DEALER') {
            $request->session()->put('user_id', 0);
            $request->session()->put('user_email', 'dealer@demo');
            $request->session()->put('user_role', 'dealer');
            return redirect('/dealer/dashboard');
        }

        // Database login
        $row = DB::selectOne(
            'SELECT "UserID", "PasswordHash", "SystemRole", "IsActive" FROM "Users" WHERE "Email" = ?',
            [$email]
        );

        if (!$row) {
            return back()->withInput($request->only('email'))->with('error', 'Invalid email or password.');
        }
        if (!$row->IsActive) {
            return back()->withInput($request->only('email'))->with('error', 'Account is deactivated.');
        }
        if ($row->PasswordHash !== $password) {
            return back()->withInput($request->only('email'))->with('error', 'Invalid email or password.');
        }

        DB::update('UPDATE "Users" SET "LastLogin" = NOW() WHERE "UserID" = ?', [$row->UserID]);

        $role = $row->SystemRole === 'Admin' ? 'admin' : 'dealer';
        $request->session()->put('user_id', $row->UserID);
        $request->session()->put('user_email', $email);
        $request->session()->put('user_role', $role);

        return $role === 'admin' ? redirect('/admin/dashboard') : redirect('/dealer/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
