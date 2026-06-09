<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE "USERS" DROP CONSTRAINT "UQ_USERS_EMAIL_ROLE"');
        } catch (\Exception $e) {
            // Ignore if constraint doesn't exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE "USERS" ADD CONSTRAINT "UQ_USERS_EMAIL_ROLE" UNIQUE ("EMAIL", "SYSTEMROLE")');
        } catch (\Exception $e) {
            // Ignore
        }
    }
};
