<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesConsoleRedirects;
use App\Http\Controllers\Concerns\UsesSetupLinkStore;
use App\Support\AppConstants;
use App\Support\StringHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    use ResolvesConsoleRedirects;
    use UsesSetupLinkStore;

    public function showLoginForm(Request $request): View|RedirectResponse
    {
        $request->session()->forget([
            AppConstants::SESSION_LOGIN_FAIL_COUNTS,
        ]);

        if ($request->session()->has(AppConstants::SESSION_USER_ID) && $request->session()->get(AppConstants::SESSION_PASSKEY_SETUP_REQUIRED)) {
            $role = StringHelper::normalize($request->session()->get(AppConstants::SESSION_USER_ROLE));

            return view('auth.login', [
                'show_register_passkey' => true,
                'passkey_setup_required' => true,
                'dashboard_url' => $this->dashboardPathForRole($request, $role),
            ]);
        }

        if ($request->session()->has(AppConstants::SESSION_USER_ROLE)) {
            $role = $request->session()->get(AppConstants::SESSION_USER_ROLE);
            return redirect($this->dashboardPathForRole($request, StringHelper::normalize($role)));
        }

        return view('auth.login', [
            'show_test_login_shortcuts' => $this->testLoginShortcutsEnabled(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        return $this->passkeyOnlyLoginRedirect();
    }

    public function showPasskeySetupForm(Request $request): View|RedirectResponse
    {
        $token = trim((string) $request->query('token', ''));
        $userId = $this->setupLinkStore()->resolveSetupToken($token);
        if ($token === '' || $userId === null) {
            return $this->invalidPasskeySetupLinkView('Invalid or expired passkey setup link.');
        }

        $row = DB::selectOne(
            'SELECT "USERID", "EMAIL", "LASTLOGIN", "ISACTIVE", "ALIAS", "SYSTEMROLE" FROM "USERS" WHERE "USERID" = ?',
            [$userId]
        );
        if (!$row || !$row->ISACTIVE) {
            $this->setupLinkStore()->forgetSetupToken($userId);
            return $this->invalidPasskeySetupLinkView('This passkey setup link is no longer valid.');
        }

        $role = $this->systemRoleToSessionRole((string) ($row->SYSTEMROLE ?? ''));

        $request->session()->forget([
            'user_id',
            'user_email',
            'user_alias',
            'user_role',
            'passkey_manage_passkey_id',
            'url.intended',
            'show_register_passkey',
            'last_activity_ts',
        ]);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        $request->session()->put('user_id', (string) $row->USERID);
        $request->session()->put('user_email', (string) ($row->EMAIL ?? ''));
        $request->session()->put('user_alias', (string) ($row->ALIAS ?? ''));
        $request->session()->put('user_role', $role);
        $request->session()->put('passkey_setup_required', true);
        $request->session()->put('passkey_setup_token_user_id', (string) $row->USERID);

        return redirect()->route('login')->with('success', 'Set up your passkey to finish activating this account.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function testingLogin(Request $request, string $role): RedirectResponse
    {
        if (!$this->testLoginShortcutsEnabled()) {
            return redirect()->route('login')->with('error', 'Test login shortcuts are disabled.');
        }

        $role = strtolower(trim($role));
        if (!in_array($role, ['admin', 'dealer'], true)) {
            return redirect()->route('login')->with('error', 'Unsupported test login target.');
        }

        $row = $this->findTestingLoginUser($role);
        if (!$row) {
            return redirect()->route('login')->with('error', 'No active ' . $role . ' user is available for test login.');
        }

        $sessionRole = $this->systemRoleToSessionRole((string) ($row->SYSTEMROLE ?? ''));

        $request->session()->forget([
            'user_id',
            'user_email',
            'user_alias',
            'user_role',
            'passkey_manage_passkey_id',
            'url.intended',
            'show_register_passkey',
            'last_activity_ts',
            'passkey_setup_required',
            'passkey_setup_token_user_id',
        ]);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        $request->session()->put('user_id', (string) $row->USERID);
        $request->session()->put('user_email', (string) ($row->EMAIL ?? ''));
        $request->session()->put('user_alias', (string) ($row->ALIAS ?? ''));
        $request->session()->put('user_role', $sessionRole);
        $request->session()->put('last_activity_ts', time());

        return redirect($this->dashboardPathForRole($request, $sessionRole))
            ->with('success', 'Test login as ' . strtoupper($role) . ' is active.');
    }

    private function invalidPasskeySetupLinkView(string $message): View
    {
        return view('auth.passkey-message', [
            'message' => $message,
            'pageTitle' => 'Passkey Setup Link Invalid - SQL Sales Management System',
            'subtitle' => 'Set Up Passkey',
            'helperText' => 'Please request a new passkey setup link.',
        ]);
    }

    private function passkeyOnlyLoginRedirect(): RedirectResponse
    {
        return redirect()->route('login')->with(
            'error',
            'This project uses passkey sign-in only. Use Login with passkey or request a new passkey setup link.'
        );
    }

    private function testLoginShortcutsEnabled(): bool
    {
        return (bool) config('app.test_login_shortcuts', false);
    }

    private function findTestingLoginUser(string $role): ?object
    {
        $userId = trim((string) ($role === 'admin'
            ? config('app.test_login_admin_user', 'U001')
            : config('app.test_login_dealer_user', 'U032')));

        if ($userId === '') {
            return null;
        }

        return DB::selectOne(
            'SELECT FIRST 1 "USERID", "EMAIL", "ALIAS", "SYSTEMROLE"
             FROM "USERS"
             WHERE "ISACTIVE" = TRUE
               AND CAST("USERID" AS VARCHAR(50)) = ?',
            [$userId]
        );
    }

}
