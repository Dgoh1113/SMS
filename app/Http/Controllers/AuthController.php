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
            'SELECT "USERID", "EMAIL", "LASTLOGIN", "ISACTIVE", "ALIAS", "SYSTEMROLE", "COMPANY" FROM "USERS" WHERE "USERID" = ?',
            [$userId]
        );

        if (! $row) {
            $this->setupLinkStore()->forgetSetupToken($userId);

            return $this->invalidPasskeySetupLinkView('This passkey setup link is no longer valid.');
        }

        if (! $row->ISACTIVE) {
            $this->setupLinkStore()->forgetSetupToken($userId);

            return $this->invalidPasskeySetupLinkView('Your account is currently inactive. Please contact support.');
        }

        // Check if someone has already registered a passkey for this account
        $existingPasskey = DB::selectOne(
            'SELECT "USER_PASSKEYID" FROM "USER_PASSKEY" WHERE "USERID" = ?',
            [$userId]
        );
        if ($existingPasskey) {
            $this->setupLinkStore()->forgetSetupToken($userId);
            return $this->invalidPasskeySetupLinkView('Invalid or expired passkey setup link.');
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

        // Extract the specific email that clicked the link (if provided in URL)
        $clickedEmail = trim((string) $request->query('e', ''));
        if ($clickedEmail === '' || ! filter_var($clickedEmail, FILTER_VALIDATE_EMAIL)) {
            // Fallback to the first email in the DB list if not provided in URL
            $storedEmail = trim((string) ($row->EMAIL ?? ''));
            $allEmails = array_filter(array_map('trim', explode(',', $storedEmail)), fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));
            $clickedEmail = ! empty($allEmails) ? reset($allEmails) : $storedEmail;
        }

        $request->session()->put('user_id', (string) $row->USERID);
        $request->session()->put('user_email', $clickedEmail);
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

    public function showSelectCompanyForm(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('pending_login_email');
        $userIds = $request->session()->get('pending_login_user_ids');

        if (!$email || !$userIds || !is_array($userIds) || empty($userIds)) {
            return redirect()->route('login');
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $companies = DB::select(
            'SELECT "USERID", "COMPANY", "ALIAS", "SYSTEMROLE" FROM "USERS" WHERE "USERID" IN ('.$placeholders.') ORDER BY "COMPANY" ASC, "ALIAS" ASC',
            $userIds
        );

        return view('auth.select-company', [
            'email' => $email,
            'companies' => $companies
        ]);
    }

    public function selectCompany(Request $request): RedirectResponse
    {
        $email = $request->session()->get('pending_login_email');
        $userIds = $request->session()->get('pending_login_user_ids');
        $passkeyAutoId = $request->session()->get('pending_passkey_manage_id');

        if (!$email || !$userIds || !is_array($userIds) || empty($userIds)) {
            return redirect()->route('login')->with('error', 'Login session expired. Please try again.');
        }

        $selectedUserId = $request->input('user_id');
        if (!in_array($selectedUserId, $userIds)) {
            return redirect()->back()->with('error', 'Invalid company selected.');
        }

        $user = DB::selectOne(
            'SELECT "USERID", "EMAIL", "SYSTEMROLE", "ISACTIVE", "ALIAS" FROM "USERS" WHERE "USERID" = ?',
            [$selectedUserId]
        );

        if (!$user || !$user->ISACTIVE) {
            return redirect()->route('login')->with('error', 'Account not found or inactive.');
        }

        DB::update('UPDATE "USERS" SET "LASTLOGIN" = CURRENT_TIMESTAMP WHERE "USERID" = ?', [$selectedUserId]);

        $request->session()->forget([
            'pending_login_email',
            'pending_login_user_ids',
            'pending_passkey_manage_id'
        ]);

        $request->session()->put('user_id', $user->USERID);
        $request->session()->put('user_email', $user->EMAIL);
        $request->session()->put('user_alias', $user->ALIAS ?? '');
        if ($passkeyAutoId) {
            $request->session()->put('passkey_manage_passkey_id', (string) $passkeyAutoId);
        }
        $role = $this->systemRoleToSessionRole((string) ($user->SYSTEMROLE ?? ''));
        $request->session()->put('user_role', $role);
        $request->session()->forget([
            'passkey_setup_required',
            'passkey_setup_token_user_id',
            'show_register_passkey',
        ]);

        return redirect($this->dashboardPathForRole($request, $role));
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

    public function showEmergencyAdminForm(Request $request): View|RedirectResponse
    {
        $hasAdmin = DB::table('USERS')
            ->where('SYSTEMROLE', 'Admin')
            ->exists();

        if ($hasAdmin) {
            return redirect()->route('login')->with('error', 'An Admin already exists. Emergency setup aborted.');
        }

        return view('auth.emergency-admin');
    }

    public function processEmergencyAdmin(Request $request): RedirectResponse
    {
        $hasAdmin = DB::table('USERS')
            ->where('SYSTEMROLE', 'Admin')
            ->exists();

        if ($hasAdmin) {
            return redirect()->route('login')->with('error', 'An Admin already exists. Emergency setup aborted.');
        }

        $request->validate([
            'email' => 'required|email|max:120',
        ]);

        $email = $request->input('email');

        $userId = 'U001';
        try {
            $gen = DB::selectOne('SELECT GEN_ID(GEN_USERID, 1) AS ID FROM RDB$DATABASE');
            if ($gen && isset($gen->ID)) {
                $userId = sprintf('U%03d', $gen->ID);
            }
        } catch (\Exception $e) {
            // fallback
        }

        DB::table('USERS')->insert([
            'USERID' => $userId,
            'EMAIL' => $email,
            'SYSTEMROLE' => 'Admin',
            'ISACTIVE' => 1,
            'COMPANY' => 'System Admin',
            'ALIAS' => 'Super Admin',
            'POSTCODE' => '00000',
            'CITY' => 'HQ',
            'CREATIONDATE' => now(),
        ]);

        $token = $this->setupLinkStore()->issueSetupToken($userId);

        return redirect()->route('passkey.setup.form', ['token' => $token])
            ->with('success', 'Emergency Admin created successfully. Please complete your passkey setup.');
    }
}
