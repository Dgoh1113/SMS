<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $rows = DB::select('
        SELECT 
            RDB$FIELD_NAME as FIELD_NAME,
            RDB$NULL_FLAG as NULL_FLAG
        FROM RDB$RELATION_FIELDS
        WHERE RDB$RELATION_NAME = \'LEAD_ACT\'
    ');
    foreach ($rows as $row) {
        echo trim($row->FIELD_NAME) . " (nullable: " . ($row->NULL_FLAG ? 'NO' : 'YES') . ")\n";
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}
