<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * User_Passkey: AutoID, UserID, Nickname, Credential, CreationDate
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('
                CREATE TABLE IF NOT EXISTS "User_Passkey" (
                    "AutoID" SERIAL PRIMARY KEY,
                    "UserID" INTEGER NOT NULL,
                    "Nickname" VARCHAR(255) NOT NULL,
                    "Credential" TEXT NOT NULL,
                    "CreationDate" TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
            ');
            DB::statement('CREATE INDEX IF NOT EXISTS "User_Passkey_UserID_idx" ON "User_Passkey" ("UserID")');
        } else {
            Schema::create('User_Passkey', function ($table) {
                $table->id();
                $table->unsignedBigInteger('UserID');
                $table->string('Nickname');
                $table->text('Credential');
                $table->timestamp('CreationDate')->useCurrent();
                $table->index('UserID');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('DROP TABLE IF EXISTS "User_Passkey"');
        } else {
            Schema::dropIfExists('User_Passkey');
        }
    }
};
