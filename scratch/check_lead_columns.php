<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Check columns of LEAD_ACT table (assuming Firebird based on previous scratch file)
    $rows = DB::select('
        SELECT 
            RDB$FIELD_NAME as FIELD_NAME
        FROM RDB$RELATION_FIELDS
        WHERE RDB$RELATION_NAME = \'LEAD_ACT\'
    ');
    echo "Columns in LEAD_ACT:\n";
    foreach ($rows as $row) {
        echo "- " . trim($row->FIELD_NAME) . "\n";
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}
