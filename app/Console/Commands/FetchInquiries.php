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

                    // 1. Map Product ID (combining ProductInterest and Type)
                    $productId = $this->mapProducts(($data['ProductInterest'] ?? '') . ' ' . ($data['Type'] ?? ''));

                    // 2. Map Demo Mode (MUST BE 'Zoom' or 'On-site')
                    $rawDemo = strtolower($data['DemoMode'] ?? '');
                    $demoMode = 'Zoom'; // Default
                    if (str_contains($rawDemo, 'onsite') || str_contains($rawDemo, 'on-site')) {
                        $demoMode = 'On-site';
                    }

                    // 3. Map and format Existing Software
                    $acc = trim($data['ExistingSoftwareAcc'] ?? '');
                    $pay = trim($data['ExistingSoftwarePay'] ?? '');
                    $extParts = [];
                    if (!empty($acc)) {
                        if (in_array(strtolower($acc), ['na', 'n/a', 'none', '-'])) {
                            $extParts[] = $acc;
                        } else {
                            $extParts[] = "{$acc}(ACC)";
                        }
                    }
                    if (!empty($pay)) {
                        if (in_array(strtolower($pay), ['na', 'n/a', 'none', '-'])) {
                            $extParts[] = $pay;
                        } else {
                            $extParts[] = "{$pay}(PAY)";
                        }
                    }
                    if (!empty($extParts)) {
                        $existingSoftware = implode(', ', $extParts);
                    } else {
                        $existingSoftware = $data['ExistingSoftware'] ?? '';
                    }

                    // 4. Map and format Description / Message
                    $empNo = trim($data['EmployeeNo'] ?? '');
                    $reason = trim($data['Reason'] ?? '');
                    $msg = trim($data['Message'] ?? '');
                    
                    // Decode common HTML entities that might have survived the parser
                    $empNo = $this->sanitizeEncoding(html_entity_decode(str_replace('&lt;', '<', $empNo), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $reason = $this->sanitizeEncoding(html_entity_decode(str_replace('&lt;', '<', $reason), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $msg = $this->sanitizeEncoding(html_entity_decode(str_replace('&lt;', '<', $msg), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                    $descParts = [];
                    if (!empty($empNo)) {
                        $descParts[] = "Employee No: " . $empNo;
                    }
                    if (!empty($reason)) {
                        $descParts[] = "Message: " . $reason;
                    }
                    if (!empty($msg)) {
                        $descParts[] = "Message: " . $msg;
                    }
                    $description = implode(', ', $descParts);

                    // 5. Exact Truncation based on Schema
                    $companyName      = Str::limit($data['CompanyName'] ?? '', 50, '');
                    $contactName      = Str::limit($data['ContactName'] ?? '', 100, '');
                    $contactNo        = Str::limit($data['ContactNo'] ?? '', 15, '');
                    $email            = Str::limit($data['Email'] ?? '', 50, '');
                    $city             = Str::limit($data['City'] ?? '', 20, '');
                    $postcode         = Str::limit($data['Postcode'] ?? '', 5, '');
                    $businessNature   = Str::limit($data['BusinessNature'] ?? '', 30, '');
                    $existingSoftware = Str::limit($existingSoftware, 40, '');
                    $description      = Str::limit($description, 500, '');
                    $country          = Str::limit($data['Country'] ?? 'MY', 100, '');
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
                            $data['Referral'] ?? '',
                            $state,
                            'U001'
                        ]
                    );

                    $res = DB::selectOne('SELECT GEN_ID(GEN_LEADID, 0) as id FROM RDB$DATABASE');
                    $leadId = $res->id ?? $res->ID ?? null;

                    DB::commit();
                    
                    $this->info("Successfully imported Lead #{$leadId}: {$companyName}");

                    $message->move('Trash');
                    
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
     * Order-independent, robust email body parser.
     */
    private function parseEmailBody($body)
    {
        $fields = [
            'CompanyName', 'ContactName', 'ContactNo', 'Email', 'City', 'Postcode', 
            'Country', 'State', 'BusinessNature', 'ProductInterest', 'Type', 
            'ExistingSoftwareAcc', 'ExistingSoftwarePay', 'ExistingSoftware',
            'EmployeeNo', 'UserCount', 'Reason', 'DemoMode', 'Message', 
            'HowKnowUs', 'Referral', 'Campaign'
        ];

        $data = [];
        
        $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $body = strip_tags($body);
        $body = str_replace(["\r", "\n"], " \n ", $body); 
        
        $matches = [];
        foreach ($fields as $field) {
            $pos = stripos($body, $field . ':');
            if ($pos !== false) {
                $matches[] = [
                    'field' => $field,
                    'pos' => $pos
                ];
            }
        }
        
        usort($matches, function($a, $b) {
            return $a['pos'] <=> $b['pos'];
        });
        
        for ($i = 0; $i < count($matches); $i++) {
            $current = $matches[$i];
            $start = $current['pos'] + strlen($current['field']) + 1; 
            
            if ($i + 1 < count($matches)) {
                $end = $matches[$i + 1]['pos'];
                $val = substr($body, $start, $end - $start);
            } else {
                $val = substr($body, $start);
            }
            
            $val = trim($val);
            $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $val = $this->sanitizeEncoding($val);
            $val = preg_replace('/\s+/', ' ', $val); 
            
            if (str_starts_with($val, '[') && str_ends_with($val, ']')) {
                $val = '';
            }
            $data[$current['field']] = $val;
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
            'SQL Accounting' => '1',
            'SQL Payroll' => '2',
            'SQL Production' => '3',
            'SQL X-Mobile' => '4',
            'SQL Mobile App' => '4',
            'SQL eCommerce' => '5',
            'SQL Ecommerce' => '5',
            'SQL EBI Wellness POS' => '6',
            'SQL EBI POS' => '6',
            'SQL x SuDu.Ai' => '7',
            'SQL X Suduai' => '7',
            'SQL X-Store' => '8',
            'SQL Vision' => '9',
            'SQL HRMS' => '10',
            'SQL CTOS' => '11',
            'SQL API' => '12',
            'Others' => '13',
        ];

        $foundIds = [];
        foreach ($mapping as $name => $id) {
            if (stripos($typeString, $name) !== false) {
                $foundIds[] = $id;
            }
        }
        return !empty($foundIds) ? implode(',', array_unique($foundIds)) : '1';
    }

    /**
     * Clean encoding issues by replacing special characters with standard ASCII equivalents.
     */
    private function sanitizeEncoding($str)
    {
        $replacements = [
            '—' => '-', // em-dash (U+2014)
            '–' => '-', // en-dash (U+2013)
            '“' => '"',
            '”' => '"',
            '‘' => "'",
            '’' => "'",
            '…' => '...',
            "\xc2\xa0" => ' ', // non-breaking space
        ];
        return strtr($str, $replacements);
    }
}
