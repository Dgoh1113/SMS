<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesConsoleRedirects;
use App\Http\Controllers\Concerns\UsesSetupLinkStore;
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
            'login_fail_counts',
        ]);

        if ($request->session()->has('user_id') && $request->session()->get('passkey_setup_required')) {
            $role = (string) $request->session()->get('user_role', '');

            return view('auth.login', [
                'show_register_passkey' => true,
                'passkey_setup_required' => true,
                'dashboard_url' => $this->dashboardPathForRole($request, $role),
            ]);
        }

        if ($request->session()->has('user_role')) {
            $role = $request->session()->get('user_role');
            return redirect($this->dashboardPathForRole($request, (string) $role));
        }

        return view('auth.login');
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

}
