<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- DATABASE COUNT CHECK ---\n";

try {
    // 1. Total rows in LEAD
    $totalRows = DB::selectOne('SELECT COUNT(*) as cnt FROM "LEAD"');
    echo "1. Total rows in LEAD table: " . ($totalRows->cnt ?? $totalRows->CNT ?? 0) . "\n";

    // 2. Rows where isDeleted is false (User's manual query)
    // Note: This might skip NULLs depending on how it's written
    $isDeletedFalse = DB::selectOne('SELECT COUNT(*) as cnt FROM "LEAD" WHERE "ISDELETED" = FALSE');
    echo "2. Rows where ISDELETED = FALSE: " . ($isDeletedFalse->cnt ?? $isDeletedFalse->CNT ?? 0) . "\n";

    // 3. Rows where isDeleted is NULL
    $isDeletedNull = DB::selectOne('SELECT COUNT(*) as cnt FROM "LEAD" WHERE "ISDELETED" IS NULL');
    echo "3. Rows where ISDELETED IS NULL: " . ($isDeletedNull->cnt ?? $isDeletedNull->CNT ?? 0) . "\n";

    // 4. Website's logic: COALESCE(ISDELETED, FALSE) = FALSE
    $websiteBase = DB::selectOne('SELECT COUNT(*) as cnt FROM "LEAD" WHERE COALESCE("ISDELETED", FALSE) = FALSE');
    echo "4. Website base count (Not Deleted): " . ($websiteBase->cnt ?? $websiteBase->CNT ?? 0) . "\n";

    // 5. Website's final logic (Not Deleted AND Not Cancelled)
    $websiteFinal = DB::selectOne(
        'SELECT COUNT(*) as cnt FROM "LEAD" l
         WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
         AND COALESCE((SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                       FROM "LEAD_ACT" la
                       WHERE la."LEADID" = l."LEADID"
                       ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                      ), \'\') <> \'CANCELLED\''
    );
    echo "5. Website final count (Not Cancelled): " . ($websiteFinal->cnt ?? $websiteFinal->CNT ?? 0) . "\n";

    // 6. Check for Cancelled leads specifically
    $cancelledLeads = DB::selectOne(
        'SELECT COUNT(*) as cnt FROM "LEAD" l
         WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
         AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
              FROM "LEAD_ACT" la
              WHERE la."LEADID" = l."LEADID"
              ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
             ) = \'CANCELLED\''
    );
    echo "6. Leads with status CANCELLED: " . ($cancelledLeads->cnt ?? $cancelledLeads->CNT ?? 0) . "\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
