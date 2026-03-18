<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class MaintainUserTemporaryPasswordStore
{
    private const STORAGE_PATH = 'private/maintain-user-temp-passwords.json';

    public function allDecrypted(): array
    {
        $records = $this->read();
        $decrypted = [];

        foreach ($records as $userId => $record) {
            if (!is_array($record)) {
                continue;
            }

            $password = $this->decryptValue($record['password'] ?? null);
            if ($password === null) {
                continue;
            }

            $decrypted[(string) $userId] = [
                'password' => $password,
                'created_at' => isset($record['created_at']) ? (string) $record['created_at'] : null,
                'emailed_at' => isset($record['emailed_at']) ? (string) $record['emailed_at'] : null,
            ];
        }

        return $decrypted;
    }

    public function getPassword(string $userId): ?string
    {
        $userId = trim($userId);
        if ($userId === '') {
            return null;
        }

        $records = $this->read();
        $record = $records[$userId] ?? null;
        if (!is_array($record)) {
            return null;
        }

        return $this->decryptValue($record['password'] ?? null);
    }

    public function put(string $userId, string $password): void
    {
        $userId = trim($userId);
        if ($userId === '' || $password === '') {
            return;
        }

        $records = $this->read();
        $existing = isset($records[$userId]) && is_array($records[$userId]) ? $records[$userId] : [];
        $records[$userId] = [
            'password' => Crypt::encryptString($password),
            'created_at' => $existing['created_at'] ?? now()->toIso8601String(),
            'emailed_at' => $existing['emailed_at'] ?? null,
        ];

        $this->write($records);
    }

    public function markEmailed(string $userId): void
    {
        $userId = trim($userId);
        if ($userId === '') {
            return;
        }

        $records = $this->read();
        if (!isset($records[$userId]) || !is_array($records[$userId])) {
            return;
        }

        $records[$userId]['emailed_at'] = now()->toIso8601String();
        $this->write($records);
    }

    public function forget(string $userId): void
    {
        $userId = trim($userId);
        if ($userId === '') {
            return;
        }

        $records = $this->read();
        if (!array_key_exists($userId, $records)) {
            return;
        }

        unset($records[$userId]);
        $this->write($records);
    }

    private function read(): array
    {
        $disk = Storage::disk('local');
        if (!$disk->exists(self::STORAGE_PATH)) {
            return [];
        }

        $decoded = json_decode((string) $disk->get(self::STORAGE_PATH), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function write(array $records): void
    {
        ksort($records);
        Storage::disk('local')->put(
            self::STORAGE_PATH,
            json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function decryptValue(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
