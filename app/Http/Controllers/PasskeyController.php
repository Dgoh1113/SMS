<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesConsoleRedirects;
use App\Http\Controllers\Concerns\UsesSetupLinkStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use lbuchs\WebAuthn\WebAuthn;

class PasskeyController extends Controller
{
    use ResolvesConsoleRedirects;
    use UsesSetupLinkStore;

    /**
     * Resolve rpId for WebAuthn. Localhost is normalized for development compatibility.
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

    private function resolveCurrentSignedInUser(Request $request): ?object
    {
        $sessionUserId = trim((string) $request->session()->get('user_id', ''));
        $sessionEmail = trim((string) $request->session()->get('user_email', ''));

        if ($sessionUserId !== '') {
            try {
                $row = DB::selectOne(
                    'SELECT "USERID", "EMAIL" FROM "USERS" WHERE "USERID" = ? AND ("ISACTIVE" = 1 OR "ISACTIVE" IS NULL OR "ISACTIVE" = true)',
                    [$sessionUserId]
                );
                if ($row) {
                    return $row;
                }
            } catch (\Throwable $e) {
                try {
                    $row = DB::selectOne(
                        'SELECT "USERID", "EMAIL" FROM "USERS" WHERE "USERID" = ?',
                        [$sessionUserId]
                    );
                    if ($row) {
                        return $row;
                    }
                } catch (\Throwable $e2) {
                    // Fall back to email lookup below.
                }
            }
        }

        if ($sessionEmail === '') {
            return null;
        }

        try {
            $row = DB::selectOne(
                'SELECT "USERID", "EMAIL" FROM "USERS" WHERE "EMAIL" = ? AND ("ISACTIVE" = 1 OR "ISACTIVE" IS NULL OR "ISACTIVE" = true)',
                [$sessionEmail]
            );
            if ($row) {
                return $row;
            }
        } catch (\Throwable $e) {
            // Fall back to a plain lookup.
        }

        try {
            return DB::selectOne(
                'SELECT "USERID", "EMAIL" FROM "USERS" WHERE "EMAIL" = ?',
                [$sessionEmail]
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function userPasskeySelectSql(string $driver, string $direction = 'DESC'): string
    {
        $order = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        return $driver === 'pgsql'
            ? 'SELECT "AutoID" AS pk, "UserID", "Nickname", "Credential", "CreationDate" AS creation_date FROM "User_Passkey" WHERE "UserID" = ? ORDER BY "CreationDate" ' . $order . ', "AutoID" ' . $order
            : ($driver === 'sqlsrv'
                ? 'SELECT [id] AS pk, [UserID], [Nickname], [Credential], [CreationDate] AS [creation_date] FROM [User_Passkey] WHERE [UserID] = ? ORDER BY [CreationDate] ' . $order . ', [id] ' . $order
                : ($driver === 'firebird'
                    ? 'SELECT "USER_PASSKEYID" AS "pk", "USERID" AS "UserID", "NICKNAME" AS "Nickname", "CREDENTIAL" AS "Credential", "CREATIONDATE" AS "creation_date" FROM "USER_PASSKEY" WHERE "USERID" = ? ORDER BY "CREATIONDATE" ' . $order . ', "USER_PASSKEYID" ' . $order
                    : 'SELECT id AS pk, UserID, Nickname, Credential, CreationDate AS creation_date FROM User_Passkey WHERE UserID = ? ORDER BY CreationDate ' . $order . ', id ' . $order));
    }

    private function readRowValue(object|array $row, array $keys, mixed $default = null): mixed
    {
        if (is_array($row)) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $row)) {
                    return $row[$key];
                }
            }

            $lowerMap = [];
            foreach ($row as $name => $value) {
                $lowerMap[strtolower((string) $name)] = $value;
            }

            foreach ($keys as $key) {
                $lookup = strtolower((string) $key);
                if (array_key_exists($lookup, $lowerMap)) {
                    return $lowerMap[$lookup];
                }
            }

            return $default;
        }

        foreach ($keys as $key) {
            if (isset($row->{$key}) || property_exists($row, $key)) {
                return $row->{$key};
            }
        }

        $vars = get_object_vars($row);
        $lowerMap = [];
        foreach ($vars as $name => $value) {
            $lowerMap[strtolower((string) $name)] = $value;
        }

        foreach ($keys as $key) {
            $lookup = strtolower((string) $key);
            if (array_key_exists($lookup, $lowerMap)) {
                return $lowerMap[$lookup];
            }
        }

        return $default;
    }

    private function updateStoredPasskeyCredential(string $driver, int|string $passkeyAutoId, array $credential): void
    {
        $credJson = json_encode($credential);

        if ($driver === 'pgsql') {
            DB::update('UPDATE "User_Passkey" SET "Credential" = CAST(? AS TEXT) WHERE "AutoID" = ?', [$credJson, $passkeyAutoId]);
        } elseif ($driver === 'sqlsrv') {
            DB::update('UPDATE [User_Passkey] SET [Credential] = CAST(? AS NVARCHAR(MAX)) WHERE [id] = ?', [$credJson, $passkeyAutoId]);
        } elseif ($driver === 'firebird') {
            DB::update('UPDATE "USER_PASSKEY" SET "CREDENTIAL" = ? WHERE "USER_PASSKEYID" = ?', [$credJson, $passkeyAutoId]);
        } else {
            DB::table('User_Passkey')->where('id', $passkeyAutoId)->update(['Credential' => $credJson]);
        }
    }

    private function resolveManagementOwnerPasskeyId(string $driver, string $userId): ?string
    {
        $rows = DB::select($this->userPasskeySelectSql($driver, 'ASC'), [$userId]);
        $fallbackId = null;

        foreach ($rows as $row) {
            $passkeyId = trim((string) $this->readRowValue($row, ['pk', 'PK', 'user_passkeyid', 'USER_PASSKEYID'], ''));
            if ($passkeyId === '') {
                continue;
            }

            if ($fallbackId === null) {
                $fallbackId = $passkeyId;
            }

            $credential = json_decode((string) $this->readRowValue($row, ['Credential', 'CREDENTIAL'], '{}'), true);
            if (is_array($credential) && !empty($credential['managementOwner'])) {
                return $passkeyId;
            }
        }

        return $fallbackId;
    }

    private function resolveNewestUserPasskeyId(string $driver, string $userId): ?string
    {
        $rows = DB::select($this->userPasskeySelectSql($driver, 'DESC'), [$userId]);
        if (!$rows) {
            return null;
        }

        return trim((string) $this->readRowValue($rows[0], ['pk', 'PK', 'user_passkeyid', 'USER_PASSKEYID'], '')) ?: null;
    }

    private function isSetupLinkOwnerRegistration(Request $request, string $userId): bool
    {
        $setupRequired = (bool) $request->session()->get('passkey_setup_required', false);
        $setupTokenUserId = trim((string) $request->session()->get('passkey_setup_token_user_id', ''));

        return $setupRequired
            && $setupTokenUserId !== ''
            && hash_equals($setupTokenUserId, $userId);
    }

    private function assignManagementOwnerPasskey(string $driver, string $userId, string $ownerPasskeyId): void
    {
        $rows = DB::select($this->userPasskeySelectSql($driver, 'ASC'), [$userId]);

        foreach ($rows as $row) {
            $passkeyId = trim((string) $this->readRowValue($row, ['pk', 'PK', 'user_passkeyid', 'USER_PASSKEYID'], ''));
            if ($passkeyId === '') {
                continue;
            }

            $credential = json_decode((string) $this->readRowValue($row, ['Credential', 'CREDENTIAL'], '{}'), true);
            if (!is_array($credential)) {
                $credential = [];
            }

            $credential['managementOwner'] = hash_equals($ownerPasskeyId, $passkeyId);
            $this->updateStoredPasskeyCredential($driver, $passkeyId, $credential);
        }
    }

    private function resolvePasskeyManagementContext(Request $request, object $user): array
    {
        $driver = DB::connection()->getDriverName();
        $userId = trim((string) $this->readRowValue($user, ['USERID', 'UserID'], ''));
        $ownerPasskeyId = $userId !== ''
            ? $this->resolveManagementOwnerPasskeyId($driver, $userId)
            : null;
        $currentPasskeyId = trim((string) $request->session()->get('passkey_manage_passkey_id', ''));

        return [
            'driver' => $driver,
            'user_id' => $userId,
            'owner_passkey_id' => $ownerPasskeyId,
            'current_passkey_id' => $currentPasskeyId,
            'can_manage' => $ownerPasskeyId === null
                ? true
                : ($currentPasskeyId !== '' && hash_equals((string) $ownerPasskeyId, $currentPasskeyId)),
        ];
    }

    private function formatPasskeyTimestampLabel(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('d M Y, h:i A');
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->format('d M Y, h:i A');
        } catch (\Throwable $e) {
            return $raw;
        }
    }

    /**
     * Get registration options for the currently signed-in user.
     */
    public function registerOptions(Request $request): JsonResponse
    {
        $row = $this->resolveCurrentSignedInUser($request);
        if (!$row) {
            return response()->json(['error' => 'Please sign in before registering a passkey.'], 403);
        }

        $management = $this->resolvePasskeyManagementContext($request, $row);
        $isSetupLinkOwnerRegistration = $this->isSetupLinkOwnerRegistration($request, (string) $this->readRowValue($row, ['USERID', 'UserID'], ''));
        if (!$management['can_manage'] && !$isSetupLinkOwnerRegistration) {
            return response()->json([
                'error' => 'Permission denied. Sign in using the first registered passkey to manage additional passkeys.',
            ], 403);
        }

        $userIdRaw = (string) ($row->USERID ?? '');
        // WebAuthn user.id is an opaque byte sequence. Our Firebird USERID can be non-numeric (e.g. "U001"),
        // so use a stable binary id that won't collapse to 0.
        $userIdBinary = ctype_digit($userIdRaw)
            ? pack('N', (int) $userIdRaw)
            : hash('sha256', $userIdRaw, true);
        $userName = $row->EMAIL;
        $userDisplayName = $row->EMAIL;

        $excludeCredentialIds = [];
        $driver = DB::connection()->getDriverName();
        $selectCredSql = $driver === 'pgsql'
            ? 'SELECT "Credential" FROM "User_Passkey" WHERE "UserID" = ?'
            : ($driver === 'sqlsrv'
                ? 'SELECT [Credential] FROM [User_Passkey] WHERE [UserID] = ?'
                : ($driver === 'firebird'
                    ? 'SELECT "CREDENTIAL" AS "Credential" FROM "USER_PASSKEY" WHERE "USERID" = ?'
                    : 'SELECT Credential FROM User_Passkey WHERE UserID = ?'));
        try {
            $passkeys = DB::select($selectCredSql, [$row->USERID]);
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
                'error' => 'Passkey registration is temporarily unavailable.',
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
            return response()->json(['error' => 'Passkey registration could not be started.'], 500);
        }

        $request->session()->put('passkey_challenge', $webAuthn->getChallenge());
        $request->session()->put('passkey_register_user_id', $row->USERID);
        $request->session()->put('passkey_register_email', $row->EMAIL ?? '');

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

        $currentUser = $this->resolveCurrentSignedInUser($request);
        if (!$currentUser || (string) $this->readRowValue($currentUser, ['USERID', 'UserID'], '') !== (string) $userId) {
            $request->session()->forget(['passkey_challenge', 'passkey_register_user_id', 'passkey_register_email']);
            return response()->json(['error' => 'Please sign in before registering a passkey.'], 403);
        }

        $driver = DB::connection()->getDriverName();
        $management = $this->resolvePasskeyManagementContext($request, $currentUser);
        $isSetupLinkOwnerRegistration = $this->isSetupLinkOwnerRegistration($request, (string) $userId);
        if (!$management['can_manage'] && !$isSetupLinkOwnerRegistration) {
            $request->session()->forget(['passkey_challenge', 'passkey_register_user_id', 'passkey_register_email']);
            return response()->json([
                'error' => 'Permission denied. Sign in using the first registered passkey to manage additional passkeys.',
            ], 403);
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
            return response()->json(['error' => 'Passkey verification failed. Please try again.'], 400);
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
            'lastUsedAt' => null,
            'managementOwner' => $isSetupLinkOwnerRegistration || $management['owner_passkey_id'] === null,
        ]);
        $insertedPasskeyId = null;
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
        } elseif ($driver === 'firebird') {
            // USER_PASSKEYID isn't auto-generated in this schema; allocate one.
            $row = DB::selectOne('SELECT COALESCE(MAX("USER_PASSKEYID"), 0) + 1 AS "NEXT_ID" FROM "USER_PASSKEY"');
            $nextId = (int) ($row->NEXT_ID ?? 1);
            DB::insert(
                'INSERT INTO "USER_PASSKEY" ("USER_PASSKEYID","USERID","NICKNAME","CREDENTIAL","CREATIONDATE") VALUES (?,?,?,?,CURRENT_TIMESTAMP)',
                [$nextId, $userId, $nickname, $credentialJson]
            );
            $insertedPasskeyId = (string) $nextId;
        } else {
            DB::table('User_Passkey')->insert([
                'UserID' => $userId,
                'Nickname' => $nickname,
                'Credential' => $credentialJson,
                'CreationDate' => now(),
            ]);
        }

        if ($insertedPasskeyId === null) {
            $insertedPasskeyId = $this->resolveNewestUserPasskeyId($driver, (string) $userId);
        }

        if ($insertedPasskeyId !== null && $insertedPasskeyId !== '') {
            if ($isSetupLinkOwnerRegistration || $management['owner_passkey_id'] === null) {
                $this->assignManagementOwnerPasskey($driver, (string) $userId, $insertedPasskeyId);
            }

            $newOwnerPasskeyId = $this->resolveManagementOwnerPasskeyId($driver, (string) $userId);
            if ($newOwnerPasskeyId !== null && $newOwnerPasskeyId !== '') {
                $request->session()->put('passkey_manage_passkey_id', $newOwnerPasskeyId);
            }
        }

        $redirect = null;
        $setupTokenUserId = trim((string) $request->session()->get('passkey_setup_token_user_id', ''));
        $setupRequired = (bool) $request->session()->get('passkey_setup_required', false);
        if ($setupRequired && $setupTokenUserId !== '' && hash_equals($setupTokenUserId, (string) $userId)) {
            DB::update('UPDATE "USERS" SET "LASTLOGIN" = CURRENT_TIMESTAMP WHERE "USERID" = ?', [$userId]);
            $this->setupLinkStore()->forgetSetupToken($setupTokenUserId);
            $redirect = $this->dashboardPathForRole($request, (string) $request->session()->get('user_role', 'dealer'));
        }

        $request->session()->forget([
            'passkey_challenge',
            'passkey_register_user_id',
            'passkey_register_email',
            'passkey_setup_required',
            'passkey_setup_token_user_id',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Passkey registered successfully.',
            'redirect' => $redirect,
        ]);
    }

    public function manageList(Request $request): JsonResponse
    {
        $user = $this->resolveCurrentSignedInUser($request);
        if (!$user) {
            return response()->json(['error' => 'Please sign in before managing passkeys.'], 403);
        }

        $management = $this->resolvePasskeyManagementContext($request, $user);
        if (!$management['can_manage']) {
            return response()->json([
                'error' => 'Access denied. Sign in using the first registered passkey to manage additional passkeys.',
            ], 403);
        }

        $driver = $management['driver'];
        $rows = DB::select($this->userPasskeySelectSql($driver), [$user->USERID]);

        $items = array_map(function ($row) {
            $credential = json_decode((string) $this->readRowValue($row, ['Credential', 'CREDENTIAL'], '{}'), true);
            $lastUsedAt = is_array($credential) ? ($credential['lastUsedAt'] ?? null) : null;
            $userPasskeyId = (string) $this->readRowValue($row, ['pk', 'PK', 'user_passkeyid', 'USER_PASSKEYID'], '');

            return [
                'id' => $userPasskeyId,
                'user_passkeyid' => $userPasskeyId,
                'nickname' => trim((string) $this->readRowValue($row, ['Nickname', 'NICKNAME'], '')) !== ''
                    ? trim((string) $this->readRowValue($row, ['Nickname', 'NICKNAME'], ''))
                    : 'Unnamed passkey',
                'last_use_label' => $this->formatPasskeyTimestampLabel($lastUsedAt) ?? 'Never used',
                'created_label' => $this->formatPasskeyTimestampLabel($this->readRowValue($row, ['creation_date', 'CREATION_DATE', 'CreationDate', 'CREATIONDATE'])) ?? '-',
            ];
        }, $rows);

        return response()->json([
            'items' => $items,
        ]);
    }

    public function manageDelete(Request $request, string $passkeyId): JsonResponse
    {
        $user = $this->resolveCurrentSignedInUser($request);
        if (!$user) {
            return response()->json(['error' => 'Please sign in before managing passkeys.'], 403);
        }

        $management = $this->resolvePasskeyManagementContext($request, $user);
        if (!$management['can_manage']) {
            return response()->json([
                'error' => 'Permission denied. Sign in using the first registered passkey to manage additional passkeys.',
            ], 403);
        }

        $passkeyId = trim($passkeyId);
        if ($passkeyId === '') {
            return response()->json(['error' => 'Passkey not found.'], 404);
        }

        $driver = $management['driver'];
        try {
            $ownedPasskey = null;

            if ($driver === 'pgsql') {
                $ownedPasskey = DB::selectOne(
                    'SELECT "AutoID" AS pk FROM "User_Passkey" WHERE "AutoID" = ? AND "UserID" = ?',
                    [$passkeyId, $user->USERID]
                );
            } elseif ($driver === 'sqlsrv') {
                $ownedPasskey = DB::selectOne(
                    'SELECT [id] AS pk FROM [User_Passkey] WHERE [id] = ? AND [UserID] = ?',
                    [$passkeyId, $user->USERID]
                );
            } elseif ($driver === 'firebird') {
                $ownedPasskey = DB::selectOne(
                    'SELECT "USER_PASSKEYID" AS "pk" FROM "USER_PASSKEY" WHERE "USER_PASSKEYID" = ? AND "USERID" = ?',
                    [$passkeyId, $user->USERID]
                );
            } else {
                $ownedPasskey = DB::table('User_Passkey')
                    ->select('id as pk')
                    ->where('id', $passkeyId)
                    ->where('UserID', $user->USERID)
                    ->first();
            }

            $ownedPasskeyPk = $ownedPasskey ? $this->readRowValue($ownedPasskey, ['pk', 'PK', 'USER_PASSKEYID', 'id']) : null;
            if ($ownedPasskeyPk === null || $ownedPasskeyPk === '') {
                return response()->json(['error' => 'Passkey not found or already removed.'], 404);
            }

            if (($management['owner_passkey_id'] ?? null) !== null
                && hash_equals((string) $management['owner_passkey_id'], (string) $ownedPasskeyPk)) {
                return response()->json([
                    'error' => 'The first registered passkey cannot be removed. Use it to manage your other passkeys.',
                ], 403);
            }

            $deleted = 0;

            if ($driver === 'pgsql') {
                $deleted = DB::delete('DELETE FROM "User_Passkey" WHERE "AutoID" = ?', [$ownedPasskeyPk]);
            } elseif ($driver === 'sqlsrv') {
                $deleted = DB::delete('DELETE FROM [User_Passkey] WHERE [id] = ?', [$ownedPasskeyPk]);
            } elseif ($driver === 'firebird') {
                $deleted = DB::delete('DELETE FROM "USER_PASSKEY" WHERE "USER_PASSKEYID" = ?', [$ownedPasskeyPk]);
            } else {
                $deleted = DB::table('User_Passkey')->where('id', $ownedPasskeyPk)->delete();
            }

            if ((int) $deleted < 1) {
                return response()->json(['error' => 'Passkey could not be deleted. Please refresh and try again.'], 409);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Passkey delete failed. Please try again.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Passkey deleted successfully.',
        ]);
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
        $selectAuthSql = $driver === 'pgsql'
            ? 'SELECT "AutoID" as pk, "UserID", "Credential" FROM "User_Passkey"'
            : ($driver === 'sqlsrv'
                ? 'SELECT [id] as pk, [UserID], [Credential] as [Credential] FROM [User_Passkey]'
                : ($driver === 'firebird'
                    ? 'SELECT "USER_PASSKEYID" as "pk", "USERID", "CREDENTIAL" as "Credential" FROM "USER_PASSKEY"'
                    : 'SELECT id as pk, UserID, Credential FROM User_Passkey'));
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
                $userId = $this->readRowValue($p, ['USERID', 'UserID']);
                $passkeyAutoId = $this->readRowValue($p, ['pk', 'PK', 'USER_PASSKEYID', 'id']);
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
            return response()->json(['error' => 'Passkey verification failed. Please try again.'], 400);
        }

        $newSignCount = $webAuthn->getSignatureCounter();
        if ($passkeyAutoId !== null && $matchedCred !== null) {
            if ($newSignCount !== null) {
                $matchedCred['signatureCounter'] = $newSignCount;
            }
            $matchedCred['lastUsedAt'] = now()->toDateTimeString();
            $this->updateStoredPasskeyCredential($driver, $passkeyAutoId, $matchedCred);
        }

        $user = DB::selectOne(
            'SELECT "USERID", "EMAIL", "SYSTEMROLE", "ISACTIVE", "ALIAS" FROM "USERS" WHERE "USERID" = ?',
            [$userId]
        );

        if (!$user || !$user->ISACTIVE) {
            $request->session()->forget('passkey_challenge');
            return response()->json(['error' => 'Account not found or inactive.'], 400);
        }

        DB::update('UPDATE "USERS" SET "LASTLOGIN" = CURRENT_TIMESTAMP WHERE "USERID" = ?', [$userId]);

        $request->session()->forget('passkey_challenge');
        $request->session()->put('user_id', $user->USERID);
        $request->session()->put('user_email', $user->EMAIL);
        $request->session()->put('user_alias', $user->ALIAS ?? '');
        $request->session()->put('passkey_manage_passkey_id', (string) $passkeyAutoId);
        $role = $this->systemRoleToSessionRole((string) ($user->SYSTEMROLE ?? ''));
        $request->session()->put('user_role', $role);
        $request->session()->forget([
            'passkey_setup_required',
            'passkey_setup_token_user_id',
            'show_register_passkey',
        ]);

        $redirect = $this->dashboardPathForRole($request, $role);

        return response()->json(['success' => true, 'redirect' => $redirect]);
    }
}
