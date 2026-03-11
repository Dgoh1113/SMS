<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $pdo = app('db')->connection()->getPdo();
    echo "SUCCESS: Connected to Firebird!\n";
    echo "DB: " . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";
} catch (\Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}