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
        if ($request->session()->has('user_role')) {
            $role = $request->session()->get('user_role');
            return $role === 'admin' ? redirect('/admin/dashboard') : redirect('/dealer/dashboard');
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
            'SELECT "UserID", "PasswordHash", "SystemRole", "IsActive" FROM "Users" WHERE "Email" = ?',
            [$email]
        );

        if (!$row) {
            return back()->withInput($request->only('email'))->with('error', 'Invalid email or password.');
        }
        if (!$row->IsActive) {
            return back()->withInput($request->only('email'))->with('error', 'Account is deactivated.');
        }

        $stored = (string) ($row->PasswordHash ?? '');
        $looksHashed = str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$argon2');
        $ok = $looksHashed ? Hash::check($password, $stored) : hash_equals($stored, $password);

        if (!$ok) {
            return back()->withInput($request->only('email'))->with('error', 'Invalid email or password.');
        }

        // If legacy plaintext was stored, upgrade it to a real hash on successful login.
        if (!$looksHashed) {
            DB::update(
                'UPDATE "Users" SET "PasswordHash" = ? WHERE "UserID" = ?',
                [Hash::make($password), $row->UserID]
            );
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
