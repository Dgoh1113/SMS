<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserSetupLinkStore
{
    private const STORAGE_PATH = 'private/user-setup-links.json';

    public function allSetupLinks(): array
    {
        $records = $this->read();
        $links = [];
        $dirty = false;

        foreach ($records as $userId => $record) {
            if (!is_array($record)) {
                continue;
            }

            $expiresAt = trim((string) ($record['setup_token_expires_at'] ?? ''));
            if ($expiresAt === '') {
                continue;
            }

            if ($this->isExpired($expiresAt)) {
                unset(
                    $records[$userId]['setup_token_hash'],
                    $records[$userId]['setup_token_created_at'],
                    $records[$userId]['setup_token_emailed_at'],
                    $records[$userId]['setup_token_expires_at']
                );
                if (empty($records[$userId])) {
                    unset($records[$userId]);
                }
                $dirty = true;
                continue;
            }

            $links[(string) $userId] = [
                'created_at' => isset($record['setup_token_created_at']) ? (string) ($record['setup_token_created_at']) : null,
                'emailed_at' => isset($record['setup_token_emailed_at']) ? (string) ($record['setup_token_emailed_at']) : null,
                'expires_at' => $expiresAt,
            ];
        }

        if ($dirty) {
            $this->write($records);
        }

        return $links;
    }

    public function issueSetupToken(string $userId, int $ttlMinutes = 1440): string
    {
        $userId = trim($userId);
        if ($userId === '') {
            return '';
        }

        $records = $this->read();
        $existing = isset($records[$userId]) && is_array($records[$userId]) ? $records[$userId] : [];
        $token = Str::random(64);

        $records[$userId] = array_merge($existing, [
            'setup_token_hash' => hash('sha256', $token),
            'setup_token_created_at' => now()->toIso8601String(),
            'setup_token_emailed_at' => null,
            'setup_token_expires_at' => now()->addMinutes(max(5, $ttlMinutes))->toIso8601String(),
        ]);

        $this->write($records);

        return $token;
    }

    public function markSetupTokenEmailed(string $userId): void
    {
        $userId = trim($userId);
        if ($userId === '') {
            return;
        }

        $records = $this->read();
        if (!isset($records[$userId]) || !is_array($records[$userId])) {
            return;
        }

        $records[$userId]['setup_token_emailed_at'] = now()->toIso8601String();
        $this->write($records);
    }

    public function resolveSetupToken(string $token): ?string
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        $records = $this->read();
        $tokenHash = hash('sha256', $token);
        $dirty = false;

        foreach ($records as $userId => $record) {
            if (!is_array($record)) {
                continue;
            }

            $expiresAt = trim((string) ($record['setup_token_expires_at'] ?? ''));
            if ($expiresAt === '' || $this->isExpired($expiresAt)) {
                if ($expiresAt !== '') {
                    unset(
                        $records[$userId]['setup_token_hash'],
                        $records[$userId]['setup_token_created_at'],
                        $records[$userId]['setup_token_emailed_at'],
                        $records[$userId]['setup_token_expires_at']
                    );
                    if (empty($records[$userId])) {
                        unset($records[$userId]);
                    }
                    $dirty = true;
                }
                continue;
            }

            $storedHash = trim((string) ($record['setup_token_hash'] ?? ''));
            if ($storedHash !== '' && hash_equals($storedHash, $tokenHash)) {
                if ($dirty) {
                    $this->write($records);
                }
                return (string) $userId;
            }
        }

        if ($dirty) {
            $this->write($records);
        }

        return null;
    }

    public function forgetSetupToken(string $userId): void
    {
        $userId = trim($userId);
        if ($userId === '') {
            return;
        }

        $records = $this->read();
        if (!isset($records[$userId]) || !is_array($records[$userId])) {
            return;
        }

        unset(
            $records[$userId]['setup_token_hash'],
            $records[$userId]['setup_token_created_at'],
            $records[$userId]['setup_token_emailed_at'],
            $records[$userId]['setup_token_expires_at']
        );

        if (empty($records[$userId])) {
            unset($records[$userId]);
        }

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
        $disk = Storage::disk('local');

        ksort($records);
        $disk->put(
            self::STORAGE_PATH,
            json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function isExpired(string $value): bool
    {
        if (trim($value) === '') {
            return true;
        }

        try {
            return Carbon::parse($value)->isPast();
        } catch (\Throwable) {
            return true;
        }
    }
}
