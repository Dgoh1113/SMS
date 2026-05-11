<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\PHPIMAP\ClientManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FetchInquiries extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'inquiries:fetch';

    /**
     * The console command description.
     */
    protected $description = 'Fetch and parse inquiries - Matched exactly to LEAD table schema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cm = new ClientManager();
        
        $client = $cm->make([
            'host'          => 'mail.sql.com.my',
            'port'          => 993,
            'encryption'    => 'ssl',
            'validate_cert' => true,
            'username'      => 'smsinquiry@sql.com.my',
            'password'      => '79,coLoTErAmEsp,#',
            'protocol'      => 'imap'
        ]);

        $this->info("Connecting to mail.sql.com.my...");

        try {
            $client->connect();
            $folder = $client->getFolder('INBOX');
            $messages = $folder->query()->all()->get();

            if ($messages->count() === 0) {
                $this->info("No new inquiries found.");
                return;
            }

            $this->info("Found " . $messages->count() . " emails. Processing...");

            foreach ($messages as $message) {
                $body = $message->getTextBody() ?: $message->getHTMLBody(true);

                if (!$body) {
                    $this->warn("Mail #{$message->getUid()} has no body. Skipping.");
                    continue;
                }

                // Parsing Logic
                $data = $this->parseEmailBody($body);

                if (empty($data['CompanyName'])) {
                    $this->warn("Mail #{$message->getUid()} could not find CompanyName. Skipping.");
                    continue;
                }

                // Database Insertion
                try {
                    DB::beginTransaction();

                    // 1. Map Product ID
                    $productId = $this->mapProducts($data['Type'] ?? '');

                    // 2. Map Demo Mode (MUST BE 'Zoom' or 'On-site')
                    $rawDemo = strtolower($data['DemoMode'] ?? '');
                    $demoMode = 'Zoom'; // Default
                    if (str_contains($rawDemo, 'onsite') || str_contains($rawDemo, 'on-site')) {
                        $demoMode = 'On-site';
                    }

                    // 3. Exact Truncation based on Schema
                    $companyName      = Str::limit($data['CompanyName'] ?? '', 50, '');
                    $contactName      = Str::limit($data['ContactName'] ?? '', 100, '');
                    $contactNo        = Str::limit($data['ContactNo'] ?? '', 15, '');
                    $email            = Str::limit($data['Email'] ?? '', 50, '');
                    $city             = Str::limit($data['City'] ?? '', 20, '');
                    $postcode         = Str::limit($data['Postcode'] ?? '', 5, '');
                    $businessNature   = Str::limit($data['BusinessNature'] ?? '', 30, '');
                    $existingSoftware = Str::limit($data['ExistingSoftware'] ?? '', 40, '');
                    $referral         = Str::limit($data['Referral'] ?? '', 20, '');
                    $description      = Str::limit($data['Message'] ?? '', 160, '');
                    $country          = Str::limit($data['Country'] ?? 'MY', 100, ''); // Schema says 100
                    $state            = Str::limit($data['State'] ?? '', 100, '');

                    $userCount = isset($data['UserCount']) ? (int)$data['UserCount'] : null;

                    DB::insert(
                        'INSERT INTO "LEAD" (
                            "PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL",
                            "CITY","POSTCODE","COUNTRY","BUSINESSNATURE","USERCOUNT",
                            "EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                            "STATE","CREATEDAT","CREATEDBY","LASTMODIFIED"
                        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,?,CURRENT_TIMESTAMP)',
                        [
                            $productId,
                            $companyName,
                            $contactName,
                            $contactNo,
                            $email,
                            $city,
                            $postcode,
                            $country,
                            $businessNature,
                            $userCount,
                            $existingSoftware,
                            $demoMode,
                            $description,
                            $referral,
                            $state,
                            999
                        ]
                    );

                    // Get the generated Lead ID (Firebird often returns results in uppercase)
                    $res = DB::selectOne('SELECT GEN_ID(GEN_LEADID, 0) as id FROM RDB$DATABASE');
                    $leadId = $res->id ?? $res->ID ?? null;

                    // Note: No need to insert into LEAD_ACT manually if your triggers already do it!
                    // Your schema showed: CREATE TRIGGER TRD_LEAD_AFTER_INSERT ... 
                    // This trigger automatically creates the "Lead Created" record in LEAD_ACT.
                    
                    DB::commit();
                    
                    $this->info("Successfully imported Lead #{$leadId}: {$companyName}");

                    // 4. Delete email from server
                    $message->delete();
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Failed to import '{$data['CompanyName']}': " . $e->getMessage());
                    Log::error("Lead Import Error: " . $e->getMessage());
                }
            }

            $client->expunge();
            $this->info("Done.");

        } catch (\Exception $e) {
            $this->error("Connection error: " . $e->getMessage());
            Log::error("Mailbox Connection Error: " . $e->getMessage());
        }
    }

    /**
     * Improved Parser
     */
    private function parseEmailBody($body)
    {
        $fields = [
            'CompanyName', 'ContactName', 'ContactNo', 'Email', 'City', 'Postcode', 
            'Country', 'BusinessNature', 'UserCount', 'Platform', 'Type', 
            'ExistingSoftware', 'DemoMode', 'Message', 'HowKnowUs', 'Referral', 'Campaign'
        ];

        $data = [];
        
        $body = strip_tags($body);
        $body = str_replace(["\r", "\n"], " ", $body); 
        $body = preg_replace('/\s+/', ' ', $body);

        foreach ($fields as $index => $field) {
            $nextField = $fields[$index + 1] ?? null;
            
            if ($nextField) {
                $pattern = '/' . preg_quote($field) . ':(.*?)(?=' . preg_quote($nextField) . ':|$)/i';
            } else {
                $pattern = '/' . preg_quote($field) . ':(.*)$/i';
            }

            if (preg_match($pattern, $body, $matches)) {
                $val = trim($matches[1]);
                // If it looks like a placeholder, clear it
                if (str_starts_with($val, '[') && str_ends_with($val, ']')) {
                    $val = '';
                }
                $data[$field] = $val;
            }
        }

        return $data;
    }

    /**
     * Map product names to IDs.
     */
    private function mapProducts($typeString)
    {
        $mapping = [
            'SQL Account' => '1',
            'SQL Payroll' => '2',
            'SQL Production' => '3',
        ];

        $foundIds = [];
        foreach ($mapping as $name => $id) {
            if (stripos($typeString, $name) !== false) {
                $foundIds[] = $id;
            }
        }

        return !empty($foundIds) ? implode(',', $foundIds) : '1';
    }
}
