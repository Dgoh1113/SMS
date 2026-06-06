<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncDealers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dealers:sync';

    /**
     * The console command description.
     */
    protected $description = 'Sync dealers from SQL Account API and update the USERS table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $config = config('services.sql_account');
        $baseUrl = $config['base_url'] ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        if (!$baseUrl || !$username || !$password) {
            $this->error("API credentials missing.");
            return 1;
        }

        $limit = 50;
        $offset = 0;
        $dealersToSync = [];

        $this->line('<fg=green>Fetching customers from SQL Account API...</>');
        try {
            while (true) {
                $response = Http::timeout(15)
                    ->withBasicAuth($username, $password)
                    ->withoutVerifying()
                    ->get($baseUrl . '/customer', [
                        'limit' => $limit,
                        'offset' => $offset
                    ]);

                if (!$response->successful()) {
                    $body = $response->body();
                    // If the API returns 400 indicating no more records, handle it gracefully
                    if ($response->status() === 400 && (str_contains($body, 'No record') || str_contains($body, 'No more record'))) {
                        break;
                    }
                    $this->line("<fg=red>API Request failed at offset $offset: " . $response->status() . "</>");
                    break;
                }

                $data = $response->json();
                $list = $data['data'] ?? [];
                if (empty($list)) {
                    break;
                }

                foreach ($list as $c) {
                    $dealersToSync[] = $c;
                }

                $offset += count($list);
            }

            $this->line("<fg=green>Found " . count($dealersToSync) . " customers to sync.</>");
            
            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($dealersToSync as $d) {
              try {
                $code = trim($d['code'] ?? '');
                $companyName = trim($d['companyname'] ?? '');
                $area = strtoupper(trim($d['area'] ?? ''));
                $status = strtoupper(trim($d['status'] ?? 'A'));
                $isActive = ($status === 'A' && $area !== 'INCATIVE D') ? 1 : 0;

                if (empty($code)) {
                    continue;
                }

                $detailResponse = Http::timeout(15)
                    ->withBasicAuth($username, $password)
                    ->withoutVerifying()
                    ->get($baseUrl . '/customer/' . rawurlencode($code));

                if (!$detailResponse->successful()) {
                    $this->error("Failed to fetch details for $code: " . $detailResponse->status());
                    $skipped++;
                    continue;
                }

                $detailData = $detailResponse->json()['data'][0] ?? null;
                if (!$detailData) {
                    $this->error("No details data found for $code.");
                    $skipped++;
                    continue;
                }

                // Extract email, postcode, city from sdsbranch
                $branches = $detailData['sdsbranch'] ?? [];
                $emailRaw = '';
                $postcode = '';
                $city = '';

                foreach ($branches as $br) {
                    if (!empty($br['email'])) {
                        $emailRaw = $br['email'];
                        $postcode = $br['postcode'] ?? '';
                        $city = $br['city'] ?? '';
                        break;
                    }
                }
                if (empty($emailRaw) && !empty($branches)) {
                    $emailRaw = $branches[0]['email'] ?? '';
                    $postcode = $branches[0]['postcode'] ?? '';
                    $city = $branches[0]['city'] ?? '';
                }

                // Clean and validate email – collect ALL valid emails as comma-separated
                $validEmails = [];
                if (!empty($emailRaw)) {
                    // Split by comma or semicolon
                    $parts = preg_split('/[,;]/', $emailRaw);
                    foreach ($parts as $part) {
                        $cleaned = trim(filter_var(trim($part), FILTER_SANITIZE_EMAIL));
                        if (filter_var($cleaned, FILTER_VALIDATE_EMAIL)) {
                            $validEmails[] = $cleaned;
                        }
                    }
                }
                $email = implode(',', $validEmails);

                // Apply strict Firebird schema column limits to prevent truncation exceptions
                $code = Str::limit($code, 50, '');
                $companyName = Str::limit($companyName, 40, '');
                $email = !empty($email) ? Str::limit($email, 120, '') : strtolower($code) . '@noemail.local';
                $postcode = Str::limit(preg_replace('/\D/', '', $postcode), 5, '');
                $city = Str::limit($city, 100, '');

                // Check if dealer code already exists in ALIAS
                $existingByCode = DB::selectOne(
                    'SELECT "USERID", "EMAIL", "LASTLOGIN" FROM "USERS" WHERE UPPER(TRIM("ALIAS")) = UPPER(TRIM(?))',
                    [$code]
                );

                if ($existingByCode) {
                    // Skip EMAIL overwrite if the user has already registered (has logged in)
                    $hasRegistered = !empty($existingByCode->LASTLOGIN);
                    if ($hasRegistered) {
                        $this->line("<fg=green>Updating existing dealer (by code, keeping registered email): $code</>");
                        DB::update(
                            'UPDATE "USERS" SET "COMPANY" = ?, "POSTCODE" = ?, "CITY" = ?, "ISACTIVE" = ? WHERE "USERID" = ?',
                            [
                                $companyName !== '' ? $companyName : null,
                                $postcode !== '' ? $postcode : '',
                                $city !== '' ? $city : '',
                                $isActive,
                                $existingByCode->USERID
                            ]
                        );
                    } else {
                        $this->line("<fg=green>Updating existing dealer (by code): $code | Email: " . ($email !== '' ? $email : 'N/A') . "</>");
                        DB::update(
                            'UPDATE "USERS" SET "EMAIL" = ?, "COMPANY" = ?, "POSTCODE" = ?, "CITY" = ?, "ISACTIVE" = ? WHERE "USERID" = ?',
                            [
                                $email,
                                $companyName !== '' ? $companyName : null,
                                $postcode !== '' ? $postcode : '',
                                $city !== '' ? $city : '',
                                $isActive,
                                $existingByCode->USERID
                            ]
                        );
                    }
                    $updated++;
                } else {
                    // Check if user with same email exists (only if email is not a placeholder)
                    $existingByEmail = null;
                    if (!str_ends_with($email, '@noemail.local')) {
                        $existingByEmail = DB::selectOne(
                            'SELECT "USERID", "ALIAS" FROM "USERS" WHERE UPPER(TRIM("EMAIL")) = UPPER(TRIM(?))',
                            [$email]
                        );
                    }

                    if ($existingByEmail) {
                        $this->line("<fg=green>Updating existing dealer (by email): $email | Code: $code</>");
                        DB::update(
                            'UPDATE "USERS" SET "ALIAS" = ?, "COMPANY" = ?, "POSTCODE" = ?, "CITY" = ?, "ISACTIVE" = ? WHERE "USERID" = ?',
                            [
                                $code,
                                $companyName !== '' ? $companyName : null,
                                $postcode !== '' ? $postcode : '',
                                $city !== '' ? $city : '',
                                $isActive,
                                $existingByEmail->USERID
                            ]
                        );
                        $updated++;
                    } else {
                        $this->line("<fg=green>Inserting new dealer: Code: $code | Company: $companyName | Email: " . ($email !== '' ? $email : 'N/A') . "</>");
                        
                        $users = DB::select('SELECT "USERID" FROM "USERS" WHERE "USERID" STARTING WITH ?', ['U']);
                        $maxNum = 0;
                        foreach ($users as $u) {
                            $uid = trim($u->USERID ?? $u->userid ?? '');
                            $num = (int) substr($uid, 1);
                            if ($num > $maxNum) {
                                $maxNum = $num;
                            }
                        }
                        $newUserId = 'U' . str_pad((string)($maxNum + 1), 3, '0', STR_PAD_LEFT);

                        DB::insert(
                            'INSERT INTO "USERS" ("USERID","EMAIL","PASSWORDHASH","SYSTEMROLE","ISACTIVE","ALIAS","COMPANY","POSTCODE","CITY") VALUES (?,?,?,?,?,?,?,?,?)',
                            [
                                $newUserId,
                                $email,
                                Hash::make(Str::random(64)),
                                'Dealer',
                                $isActive,
                                $code,
                                $companyName !== '' ? $companyName : null,
                                $postcode !== '' ? $postcode : '',
                                $city !== '' ? $city : '',
                            ]
                        );
                        $inserted++;
                    }
                }
              } catch (\Throwable $e) {
                  $this->line("<fg=red>Error syncing $code: " . $e->getMessage() . "</>");
                  $skipped++;
              }
            }

            $this->line("<fg=green>Sync complete. Inserted: $inserted, Updated: $updated, Skipped: $skipped</>");
            return 0;

        } catch (\Throwable $e) {
            $this->error("Exception during sync: " . $e->getMessage());
            Log::error("Dealer Sync Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return 1;
        }
    }
}
