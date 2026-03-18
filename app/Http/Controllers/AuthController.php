<?php

namespace App\Http\Controllers;

use App\Mail\UserPasswordResetLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        // After sign-in success we stay on login page to show register passkey; only then go to dashboard.
        if ($request->session()->has('user_id') && $request->session()->has('show_register_passkey')) {
            $role = $request->session()->get('user_role');
            $dashboardUrl = ($role === 'admin' || $role === 'manager') ? '/admin/dashboard' : '/dealer/dashboard';
            if ($role === 'dealer') {
                $intended = $request->session()->get('url.intended');
                if ($intended && str_starts_with(parse_url($intended, PHP_URL_PATH) ?: '', '/dealer/')) {
                    $dashboardUrl = $intended;
                }
            }
            return view('auth.login', ['show_register_passkey' => true, 'dashboard_url' => $dashboardUrl]);
        }
        if ($request->session()->has('user_role')) {
            $role = $request->session()->get('user_role');
            if ($role === 'admin' || $role === 'manager') {
                return redirect('/admin/dashboard');
            }
            if ($role === 'dealer') {
                $intended = $request->session()->get('url.intended');
                if ($intended && str_starts_with(parse_url($intended, PHP_URL_PATH) ?: '', '/dealer/')) {
                    $request->session()->forget('url.intended');
                    return redirect($intended);
                }
                return redirect('/dealer/dashboard');
            }
        }
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email address format.',
            'password.required' => 'Password is required.',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $emailKey = strtolower(trim((string) $email));

        // Database login
        $row = DB::selectOne(
            'SELECT "USERID", "PASSWORDHASH", "SYSTEMROLE", "ISACTIVE", "ALIAS" FROM "USERS" WHERE "EMAIL" = ?',
            [$email]
        );

        if (!$row) {
            $showResetPassword = $this->recordLoginFailure($request, $emailKey);
            return back()->withInput($request->only('email'))
                ->with('error', 'Invalid email or password.')
                ->with('show_reset_password', $showResetPassword);
        }
        if (!$row->ISACTIVE) {
            return back()->withInput($request->only('email'))->with('error', 'Your account has been frozen, please contact the administrator.');
        }

        $stored = (string) ($row->PASSWORDHASH ?? '');
        $looksHashed = str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$argon2');
        $ok = $looksHashed ? Hash::check($password, $stored) : hash_equals($stored, $password);

        if (!$ok) {
            $showResetPassword = $this->recordLoginFailure($request, $emailKey);
            return back()->withInput($request->only('email'))
                ->with('error', 'Invalid email or password.')
                ->with('show_reset_password', $showResetPassword);
        }
        $this->clearLoginFailure($request, $emailKey);

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

    public function requestPasswordResetFromLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email address format.',
        ]);
        $email = trim((string) $validated['email']);

        try {
            $this->sendPasswordResetLinkByEmail($email);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()
            ->withInput(['email' => $email])
            ->with('success', 'If your email is registered in our system, we will send you the link.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function showResetPasswordForm(Request $request, string $userid): View|RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return $this->invalidResetLinkView('Invalid or expired password reset link.');
        }

        $row = DB::selectOne(
            'SELECT "USERID", "EMAIL", "PASSWORDHASH" FROM "USERS" WHERE "USERID" = ?',
            [$userid]
        );
        if (!$row) {
            return $this->invalidResetLinkView('Invalid or expired password reset link.');
        }

        $requestHash = trim((string) $request->query('hash', ''));
        $currentHash = sha1((string) ($row->PASSWORDHASH ?? ''));
        if ($requestHash === '' || !hash_equals($currentHash, $requestHash)) {
            return $this->invalidResetLinkView('This reset link has already been used or expired.');
        }

        $requestNonce = trim((string) $request->query('nonce', ''));
        $nonceCacheKey = 'user_password_reset_nonce:' . $userid;
        $latestNonce = trim((string) Cache::get($nonceCacheKey, ''));
        if ($requestNonce === '' || $latestNonce === '' || !hash_equals($latestNonce, $requestNonce)) {
            return $this->invalidResetLinkView('This reset link has already been used or expired.');
        }

        return view('auth.reset-password', [
            'userid' => (string) ($row->USERID ?? ''),
            'email' => (string) ($row->EMAIL ?? ''),
            'formAction' => $request->fullUrl(),
        ]);
    }

    public function resetPassword(Request $request, string $userid): View|RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return $this->invalidResetLinkView('Invalid or expired password reset link.');
        }

        $validated = $request->validate([
            'password' => 'required|string|min:6|max:255|confirmed',
        ]);

        $row = DB::selectOne(
            'SELECT "USERID", "PASSWORDHASH" FROM "USERS" WHERE "USERID" = ?',
            [$userid]
        );
        if (!$row) {
            return $this->invalidResetLinkView('Invalid or expired password reset link.');
        }

        $requestHash = trim((string) $request->query('hash', ''));
        $currentPasswordHash = (string) ($row->PASSWORDHASH ?? '');
        $currentHash = sha1($currentPasswordHash);
        if ($requestHash === '' || !hash_equals($currentHash, $requestHash)) {
            return $this->invalidResetLinkView('This reset link has already been used or expired.');
        }

        $requestNonce = trim((string) $request->query('nonce', ''));
        $nonceCacheKey = 'user_password_reset_nonce:' . $userid;
        $latestNonce = trim((string) Cache::get($nonceCacheKey, ''));
        if ($requestNonce === '' || $latestNonce === '' || !hash_equals($latestNonce, $requestNonce)) {
            return $this->invalidResetLinkView('This reset link has already been used or expired.');
        }

        // Single-use protection: only update if PASSWORDHASH still matches the value bound to this link.
        $updated = DB::update(
            'UPDATE "USERS" SET "PASSWORDHASH" = ? WHERE "USERID" = ? AND "PASSWORDHASH" = ?',
            [Hash::make((string) $validated['password']), $userid, $currentPasswordHash]
        );
        if ($updated !== 1) {
            return $this->invalidResetLinkView('This reset link has already been used or expired.');
        }
        Cache::forget($nonceCacheKey);

        return view('auth.reset-password-success', [
            'message' => 'Password reset successful. Please sign in with your new password.',
        ]);
    }

    private function invalidResetLinkView(string $message): View
    {
        return view('auth.reset-password-invalid', ['message' => $message]);
    }

    private function recordLoginFailure(Request $request, string $emailKey): bool
    {
        if ($emailKey === '') {
            return false;
        }
        $failCounts = $request->session()->get('login_fail_counts', []);
        if (!is_array($failCounts)) {
            $failCounts = [];
        }
        $count = (int) ($failCounts[$emailKey] ?? 0) + 1;
        $failCounts[$emailKey] = $count;
        $request->session()->put('login_fail_counts', $failCounts);

        return $count > 1;
    }

    private function clearLoginFailure(Request $request, string $emailKey): void
    {
        if ($emailKey === '') {
            return;
        }
        $failCounts = $request->session()->get('login_fail_counts', []);
        if (!is_array($failCounts) || !array_key_exists($emailKey, $failCounts)) {
            return;
        }
        unset($failCounts[$emailKey]);
        $request->session()->put('login_fail_counts', $failCounts);
    }

    private function sendPasswordResetLinkByEmail(string $email): void
    {
        $row = DB::selectOne(
            'SELECT "USERID", "EMAIL", "PASSWORDHASH", "ALIAS", "COMPANY", "ISACTIVE" FROM "USERS" WHERE UPPER(TRIM("EMAIL")) = UPPER(TRIM(?))',
            [$email]
        );
        if (!$row) {
            return;
        }

        $isActive = (bool) ($row->ISACTIVE ?? false);
        $userId = trim((string) ($row->USERID ?? ''));
        $recipientEmail = trim((string) ($row->EMAIL ?? ''));
        $passwordHash = trim((string) ($row->PASSWORDHASH ?? ''));
        if (!$isActive || $userId === '' || $recipientEmail === '' || $passwordHash === '') {
            return;
        }

        $alias = trim((string) ($row->ALIAS ?? ''));
        $company = trim((string) ($row->COMPANY ?? ''));
        $companyUpper = strtoupper($company);
        if ($companyUpper === 'E STREAM SDN BHD') {
            $recipientName = $alias !== '' ? $alias : $recipientEmail;
        } else {
            $recipientName = $company !== '' ? $company : ($alias !== '' ? $alias : $recipientEmail);
        }

        $nonce = bin2hex(random_bytes(16));
        $nonceCacheKey = 'user_password_reset_nonce:' . $userId;
        Cache::put($nonceCacheKey, $nonce, now()->addMinutes(20));

        $resetUrl = URL::temporarySignedRoute(
            'password.reset.form',
            now()->addMinutes(15),
            [
                'userid' => $userId,
                'hash' => sha1($passwordHash),
                'nonce' => $nonce,
            ]
        );

        $systemName = trim((string) config('app.name', ''));
        if ($systemName === '' || strtoupper($systemName) === 'LARAVEL') {
            $systemName = 'SQL SMS';
        }

        Mail::to($recipientEmail)->send(new UserPasswordResetLink(
            toEmail: $recipientEmail,
            recipientName: $recipientName,
            resetUrl: $resetUrl,
            systemName: $systemName
        ));
    }
}
