<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure Credential column is TEXT/NVARCHAR(MAX).
     * Fixes "Credential stored as 0" when column was created with wrong type (e.g. INT).
     */
    public function up(): void
    {
        if (!Schema::hasTable('User_Passkey')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        try {
            if ($driver === 'pgsql') {
                DB::statement('ALTER TABLE "User_Passkey" ALTER COLUMN "Credential" TYPE TEXT USING "Credential"::text');
            } elseif ($driver === 'sqlsrv') {
                DB::statement('ALTER TABLE [User_Passkey] ALTER COLUMN [Credential] NVARCHAR(MAX) NOT NULL');
            } elseif (in_array($driver, ['mysql', 'mariadb'])) {
                DB::statement('ALTER TABLE `User_Passkey` MODIFY COLUMN `Credential` LONGTEXT NOT NULL');
            }
        } catch (\Throwable $e) {
            // Column may already be correct; ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down - column type change is a fix, not reversible
    }
};
