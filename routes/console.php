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
        'SELECT
            l."LEADID",
            l."ASSIGNED_TO",
            COALESCE(
                (SELECT FIRST 1 la."STATUS"
                 FROM "LEAD_ACT" la
                 WHERE la."LEADID" = l."LEADID"
                 ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                l."CURRENTSTATUS",
                \'Pending\'
            ) AS "LATEST_STATUS"
         FROM "LEAD" l
         WHERE l."CREATEDAT" <= ?',
        [$cutoff->format('Y-m-d H:i:s')]
    );

    $leadIds = [];
    foreach ($rows as $r) {
        $leadId = (int) ($r->LEADID ?? 0);
        if ($leadId > 0) {
            $leadIds[] = $leadId;
        }
    }

    $assignmentUserMap = [];
    if (!empty($leadIds)) {
        $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
        $assignmentRows = DB::select(
            'SELECT "LEADID", "LEAD_ACTID", "USERID", "DESCRIPTION"
             FROM "LEAD_ACT"
             WHERE "LEADID" IN (' . $placeholders . ')
               AND (
                   UPPER(TRIM(COALESCE("SUBJECT", \'\'))) STARTING WITH \'LEAD ASSIGNED\'
                   OR UPPER(TRIM(COALESCE("DESCRIPTION", \'\'))) STARTING WITH \'LEAD ASSIGNED\'
               )
             ORDER BY "LEADID" ASC, "CREATIONDATE" DESC, "LEAD_ACTID" DESC',
            $leadIds
        );

        foreach ($assignmentRows as $row) {
            $leadId = (int) ($row->LEADID ?? 0);
            if ($leadId <= 0 || array_key_exists($leadId, $assignmentUserMap)) {
                continue;
            }

            $userId = trim((string) ($row->USERID ?? ''));
            if ($userId === '') {
                $desc = trim((string) ($row->DESCRIPTION ?? ''));
                if ($desc !== '' && preg_match('/Lead Assigned by\s+(\S+)\s+to\s+(\S+)/i', $desc, $m)) {
                    $userId = trim((string) ($m[1] ?? ''));
                }
            }

            if ($userId !== '') {
                $assignmentUserMap[$leadId] = $userId;
            }
        }
    }

    $count = 0;
    foreach ($rows as $r) {
        $leadId = (int) ($r->LEADID ?? 0);
        $latestStatus = strtoupper(trim((string) ($r->LATEST_STATUS ?? 'PENDING')));
        $activityUserId = trim((string) ($assignmentUserMap[$leadId] ?? ($r->ASSIGNED_TO ?? '')));
        if ($leadId <= 0) {
            continue;
        }
        if (in_array($latestStatus, ['FAILED', 'COMPLETED', 'REWARDED'], true)) {
            continue;
        }
        if ($activityUserId === '') {
            $this->error('Failed to auto-fail lead ' . $leadId . ': no assigner or assigned user available for LEAD_ACT log.');
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

            $message = 'This lead expired automatically because it has been open for more than 8 months.';

            DB::insert(
                'INSERT INTO "LEAD_ACT"
                    ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS")
                 VALUES (NEXT VALUE FOR GEN_LEAD_ACTID,?,?,CURRENT_TIMESTAMP,?,?,?,?)',
                [$leadId, $activityUserId, 'Status changed to Failed (auto after 8 months)', $message, null, 'Failed']
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

// Run auto-fail check every 2 minutes
Schedule::command('leads:auto-fail')->everyTwoMinutes()->withoutOverlapping();
