<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('leads:auto-fail', function () {
    $now = Carbon::now();
    $cutoff = $now->copy()->subMonthsNoOverflow(8);

    $this->info('Checking for leads older than ' . $cutoff->toDateString() . ' to auto-fail...');

    $rows = DB::select(
        'SELECT "LEADID"
         FROM "LEAD"
         WHERE "CREATEDAT" <= ?
           AND UPPER(TRIM("CURRENTSTATUS")) NOT IN (\'FAILED\',\'COMPLETED\',\'REWARDED\')',
        [$cutoff->format('Y-m-d H:i:s')]
    );

    $count = 0;
    foreach ($rows as $r) {
        $leadId = (int) ($r->LEADID ?? 0);
        if ($leadId <= 0) {
            continue;
        }

        DB::beginTransaction();
        try {
            DB::update(
                'UPDATE "LEAD"
                 SET "CURRENTSTATUS" = \'Failed\',
                     "LASTMODIFIED" = CURRENT_TIMESTAMP
                 WHERE "LEADID" = ?',
                [$leadId]
            );

            $message = 'This lead expired automatically because it has been open for more than 8 months without completion.';

            DB::insert(
                'INSERT INTO "LEAD_ACT"
                    ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS")
                 VALUES (NEXT VALUE FOR GEN_LEAD_ACTID,?,?,CURRENT_TIMESTAMP,?,?,?,?)',
                [$leadId, null, 'Status changed to Failed (auto)', $message, null, 'Failed']
            );

            DB::commit();
            $count++;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Failed to auto-fail lead ' . $leadId . ': ' . $e->getMessage());
        }
    }

    $this->info('Auto-fail complete. Updated ' . $count . ' lead(s).');
})->purpose('Automatically fail leads older than 8 months without completion');

// Run auto-fail check every hour
Schedule::command('leads:auto-fail')->hourly();
