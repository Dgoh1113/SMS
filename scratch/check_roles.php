<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$email = 'dgoh2004@gmail.com'; // From user's screenshot
$rows = DB::select('SELECT "USERID", "EMAIL", "SYSTEMROLE", "ALIAS" FROM "USERS" WHERE "EMAIL" = ?', [$email]);
echo "Accounts for $email:\n";
foreach ($rows as $row) {
    print_r($row);
}
