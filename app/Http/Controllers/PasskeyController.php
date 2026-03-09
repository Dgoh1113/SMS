<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\Binary\ByteBuffer;

class PasskeyController extends Controller
{
    /**
     * Same as the working SQL SMS PHP project: use "localhost" for 127.0.0.1/::1
     * so passkey works when you open http://localhost:8080 (not http://127.0.0.1:8080).
     */
    private function getRpId(Request $request): string
    {
        $host = $request->getHost();
        $h = strtolower(trim($host));
        if ($h === '127.0.0.1' || $h === '::1' || $h === '[::1]') {
            return 'localhost';
        }
        $configured = (string) (config('passkeys.rp_id') ?? '');
        if ($configured !== '') {
            return $configured;
        }
        return $host;
    }

    private function getWebAuthn(Request $request): WebAuthn
    {
        $rpId = $this->getRpId($request);
        return new WebAuthn((string) config('passkeys.rp_name', config('app.name', 'SMS')), $rpId, null, true);
    }

    /**
     * Get registration options. Only users with existing email can register.
     */
    public function registerOptions(Request $request): JsonResponse
    {
        try {
            $request->validate(['email' => 'required|email']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Please enter a valid email address.'], 400);
        }

        $email = $request->input('email');

        $row = null;
        try {
            $row = DB::selectOne(
                'SELECT "UserID", "Email" FROM "Users" WHERE "Email" = ? AND ("IsActive" = 1 OR "IsActive" IS NULL OR "IsActive" = true)',
                [$email]
            );
            if (!$row) {
                $row = DB::selectOne(
                    'SELECT "UserID", "Email" FROM "Users" WHERE "Email" = ?',
                    [$email]
                );
            }
        } catch (\Throwable $e) {
            try {
                $row = DB::selectOne(
                    'SELECT "UserID", "Email" FROM "Users" WHERE "Email" = ?',
                    [$email]
                );
            } catch (\Throwable $e2) {
                try {
                    $laravelUser = DB::selectOne('SELECT id, email FROM users WHERE email = ?', [$email]);
                    if ($laravelUser) {
                        $row = (object) ['UserID' => $laravelUser->id, 'Email' => $laravelUser->email];
                    }
                } catch (\Throwable $e3) {
                    return response()->json([
                        'error' => 'Registration could not start. Check that your database has a "Users" table (or "users") with user id and email.',
                    ], 500);
                }
            }
        }

        if (!$row) {
            return response()->json(['error' => 'No account found for this email. Use the exact email in your "Users" table (e.g. weijiansql@gmail.com).'], 400);
        }

        $userIdBinary = pack('N', $row->UserID);
        $userName = $row->Email;
        $userDisplayName = $row->Email;

        $excludeCredentialIds = [];
        $driver = DB::connection()->getDriverName();
        $selectCredSql = $driver === 'pgsql'
            ? 'SELECT "Credential" FROM "User_Passkey" WHERE "UserID" = ?'
            : ($driver === 'sqlsrv' ? 'SELECT [Credential] FROM [User_Passkey] WHERE [UserID] = ?' : 'SELECT Credential FROM User_Passkey WHERE UserID = ?');
        try {
            $passkeys = DB::select($selectCredSql, [$row->UserID]);
            foreach ($passkeys as $p) {
                $cred = json_decode($p->Credential ?? '{}', true);
                $cid = $cred['credentialId'] ?? null;
                if (!empty($cid)) {
                    try {
                        $excludeCredentialIds[] = ByteBuffer::fromBase64Url($cid);
                    } catch (\Throwable $e) {
                        // Skip credentials in old/invalid format
                    }
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Registration could not start. Ensure the "User_Passkey" table exists (run: php artisan migrate).',
            ], 500);
        }

        try {
            $webAuthn = $this->getWebAuthn($request);
            $createArgs = $webAuthn->getCreateArgs(
                $userIdBinary,
                $userName,
                $userDisplayName,
                60,
                false,
                'preferred',
                null,
                $excludeCredentialIds
            );
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Registration could not start: ' . $e->getMessage()], 500);
        }

        $request->session()->put('passkey_challenge', $webAuthn->getChallenge());
        $request->session()->put('passkey_register_user_id', $row->UserID);
        $request->session()->put('passkey_register_email', $email);

        return response()->json($createArgs);
    }

    /**
     * Verify registration and store credential in User_Passkey.
     */
    public function registerVerify(Request $request): JsonResponse
    {
        $request->validate([
            'nickname' => 'required|string|max:255',
            'clientDataJSON' => 'required|string',
            'attestationObject' => 'required|string',
        ]);

        $challenge = $request->session()->get('passkey_challenge');
        $userId = $request->session()->get('passkey_register_user_id');

        if (!$challenge || $userId === null) {
            return response()->json(['error' => 'Session expired. Please start registration again.'], 400);
        }

        $clientDataJSON = ByteBuffer::fromBase64Url($request->input('clientDataJSON'))->getBinaryString();
        $attestationObject = ByteBuffer::fromBase64Url($request->input('attestationObject'))->getBinaryString();
        $nickname = $request->input('nickname');

        $webAuthn = $this->getWebAuthn($request);
        try {
            $data = $webAuthn->processCreate(
                $clientDataJSON,
                $attestationObject,
                $challenge,
                false,
                true,
                false,
                false
            );
        } catch (\Throwable $e) {
            $request->session()->forget(['passkey_challenge', 'passkey_register_user_id', 'passkey_register_email']);
            return response()->json(['error' => 'Verification failed: ' . $e->getMessage()], 400);
        }

        // Store both as base64url; ByteBuffer jsonSerialize uses RFC 1342 format which breaks storage/lookup.
        // getCredentialId() returns raw binary string (not ByteBuffer) - must base64url encode for JSON storage.
        $credIdBinary = $data->credentialId instanceof ByteBuffer
            ? $data->credentialId->getBinaryString()
            : (string) $data->credentialId;
        $credentialId = rtrim(strtr(base64_encode($credIdBinary), '+/', '-_'), '=');

        $credentialPublicKeyStorage = $data->credentialPublicKey instanceof ByteBuffer
            ? rtrim(strtr(base64_encode($data->credentialPublicKey->getBinaryString()), '+/', '-_'), '=')
            : (is_string($data->credentialPublicKey) ? $data->credentialPublicKey : base64_encode((string) $data->credentialPublicKey));

        $credentialJson = json_encode([
            'credentialId' => $credentialId,
            'credentialPublicKey' => $credentialPublicKeyStorage,
            'signatureCounter' => $data->signatureCounter ?? null,
        ]);
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::insert(
                'INSERT INTO "User_Passkey" ("UserID", "Nickname", "Credential", "CreationDate") VALUES (?, ?, CAST(? AS TEXT), NOW())',
                [$userId, $nickname, $credentialJson]
            );
        } elseif ($driver === 'sqlsrv') {
            DB::insert(
                'INSERT INTO [User_Passkey] ([UserID], [Nickname], [Credential], [CreationDate]) VALUES (?, ?, CAST(? AS NVARCHAR(MAX)), GETDATE())',
                [$userId, $nickname, $credentialJson]
            );
        } else {
            DB::table('User_Passkey')->insert([
                'UserID' => $userId,
                'Nickname' => $nickname,
                'Credential' => $credentialJson,
                'CreationDate' => now(),
            ]);
        }

        $request->session()->forget(['passkey_challenge', 'passkey_register_user_id', 'passkey_register_email']);

        return response()->json(['success' => true, 'message' => 'Passkey registered successfully.']);
    }

    /**
     * Get authentication options (discoverable: empty allowCredentials so browser shows all passkeys).
     */
    public function authOptions(Request $request): JsonResponse
    {
        $webAuthn = $this->getWebAuthn($request);
        $getArgs = $webAuthn->getGetArgs([], 60, true, true, true, true, true, 'preferred');

        $request->session()->put('passkey_challenge', $webAuthn->getChallenge());

        return response()->json($getArgs);
    }

    /**
     * Verify assertion and log the user in.
     */
    public function authVerify(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|string',
            'clientDataJSON' => 'required|string',
            'authenticatorData' => 'required|string',
            'signature' => 'required|string',
            'userHandle' => 'nullable|string',
        ]);

        $challenge = $request->session()->get('passkey_challenge');
        if (!$challenge) {
            return response()->json(['error' => 'Session expired. Please try again.'], 400);
        }

        $idBinary = ByteBuffer::fromBase64Url($request->input('id'))->getBinaryString();
        $clientDataJSON = ByteBuffer::fromBase64Url($request->input('clientDataJSON'))->getBinaryString();
        $authenticatorData = ByteBuffer::fromBase64Url($request->input('authenticatorData'))->getBinaryString();
        $signature = ByteBuffer::fromBase64Url($request->input('signature'))->getBinaryString();

        $driver = DB::connection()->getDriverName();
        $pkCol = $driver === 'pgsql' ? '"AutoID"' : 'id';
        $selectAuthSql = $driver === 'pgsql'
            ? "SELECT {$pkCol} as pk, \"UserID\", \"Credential\" FROM \"User_Passkey\""
            : ($driver === 'sqlsrv' ? 'SELECT [id] as pk, [UserID], [Credential] FROM [User_Passkey]' : "SELECT {$pkCol} as pk, UserID, Credential FROM User_Passkey");
        $passkeys = DB::select($selectAuthSql);
        $credentialPublicKey = null;
        $prevSignatureCnt = null;
        $userId = null;
        $passkeyAutoId = null;
        $matchedCred = null;

        foreach ($passkeys as $p) {
            $cred = json_decode($p->Credential ?? '{}', true);
            if (!$cred) {
                continue;
            }
            $storedId = $cred['credentialId'] ?? null;
            if ($storedId === null) {
                continue;
            }
            try {
                $storedIdBinary = ByteBuffer::fromBase64Url($storedId)->getBinaryString();
            } catch (\Throwable $e) {
                continue;
            }
            if ($storedIdBinary === $idBinary) {
                $storedPubKey = $cred['credentialPublicKey'] ?? null;
                if (is_string($storedPubKey)) {
                    // Stored as PEM (from getPublicKeyPem) or base64url. PEM must be passed through; base64url is raw binary (wrong).
                    if (str_contains($storedPubKey, '-----BEGIN')) {
                        $credentialPublicKey = $storedPubKey;
                    } else {
                        try {
                            $credentialPublicKey = ByteBuffer::fromBase64Url($storedPubKey)->getBinaryString();
                        } catch (\Throwable $e) {
                            $credentialPublicKey = null;
                        }
                    }
                } else {
                    $credentialPublicKey = $storedPubKey;
                }
                $prevSignatureCnt = $cred['signatureCounter'] ?? null;
                $userId = $p->UserID;
                $passkeyAutoId = $p->pk ?? null;
                $matchedCred = $cred;
                break;
            }
        }

        if (!$credentialPublicKey || $userId === null) {
            $request->session()->forget('passkey_challenge');
            return response()->json(['error' => 'Passkey not recognized.'], 400);
        }

        $webAuthn = $this->getWebAuthn($request);
        try {
            $webAuthn->processGet(
                $clientDataJSON,
                $authenticatorData,
                $signature,
                $credentialPublicKey,
                $challenge,
                $prevSignatureCnt,
                false,
                true
            );
        } catch (\Throwable $e) {
            $request->session()->forget('passkey_challenge');
            return response()->json(['error' => 'Verification failed: ' . $e->getMessage()], 400);
        }

        $newSignCount = $webAuthn->getSignatureCounter();
        if ($passkeyAutoId !== null && $newSignCount !== null && $matchedCred !== null) {
            $matchedCred['signatureCounter'] = $newSignCount;
            $credJson = json_encode($matchedCred);
            if ($driver === 'pgsql') {
                DB::update('UPDATE "User_Passkey" SET "Credential" = CAST(? AS TEXT) WHERE "AutoID" = ?', [$credJson, $passkeyAutoId]);
            } elseif ($driver === 'sqlsrv') {
                DB::update('UPDATE [User_Passkey] SET [Credential] = CAST(? AS NVARCHAR(MAX)) WHERE [id] = ?', [$credJson, $passkeyAutoId]);
            } else {
                DB::table('User_Passkey')->where('id', $passkeyAutoId)->update(['Credential' => $credJson]);
            }
        }

        $user = DB::selectOne(
            'SELECT "UserID", "Email", "SystemRole", "IsActive" FROM "Users" WHERE "UserID" = ?',
            [$userId]
        );

        if (!$user || !$user->IsActive) {
            $request->session()->forget('passkey_challenge');
            return response()->json(['error' => 'Account not found or inactive.'], 400);
        }

        DB::update('UPDATE "Users" SET "LastLogin" = NOW() WHERE "UserID" = ?', [$userId]);

        $request->session()->forget('passkey_challenge');
        $request->session()->put('user_id', $user->UserID);
        $request->session()->put('user_email', $user->Email);
        $request->session()->put('user_role', $user->SystemRole === 'Admin' ? 'admin' : 'dealer');

        $redirect = $user->SystemRole === 'Admin' ? '/admin/dashboard' : '/dealer/dashboard';

        return response()->json(['success' => true, 'redirect' => $redirect]);
    }
}
