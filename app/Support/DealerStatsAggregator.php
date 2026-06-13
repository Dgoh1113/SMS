<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * High-performance dealer statistics aggregation
 * Replaces multiple N+1 queries with single GROUP BY
 */
class DealerStatsAggregator
{
    /**
     * Get all dealer statistics - fallback to per-dealer queries
     * Uses individual queries instead of GROUP BY to ensure accurate results
     */
    public static function getAllDealerStats(array $dealerIds = []): array
    {
        // If no dealers provided, return empty
        if (empty($dealerIds)) {
            return [];
        }

        $stats = [];
        foreach ($dealerIds as $userId) {
            $userId = trim((string) $userId);
            if ($userId !== '') {
                $stats[$userId] = [
                    'totalLead' => 0,
                    'totalClosed' => 0,
                    'totalFailed' => 0,
                    'totalOngoing' => 0,
                ];
            }
        }

        try {
            $rows = DB::select(
                'SELECT
                    TRIM(CAST(l."ASSIGNEDTO" AS VARCHAR(50))) AS UID,
                    COUNT(*) AS TOTAL_LEAD,
                    SUM(CASE WHEN ls."LATEST_STATUS" IN (\'PENDING\', \'FOLLOWUP\', \'FOLLOW UP\', \'DEMO\', \'CONFIRMED\') THEN 1 ELSE 0 END) AS TOTAL_ONGOING,
                    SUM(CASE WHEN ls."LATEST_STATUS" IN (\'COMPLETED\', \'REWARDED\') THEN 1 ELSE 0 END) AS TOTAL_CLOSED,
                    SUM(CASE WHEN ls."LATEST_STATUS" = \'FAILED\' THEN 1 ELSE 0 END) AS TOTAL_FAILED
                 FROM "LEAD" l
                 LEFT JOIN (
                     SELECT a."LEADID", UPPER(TRIM(a."STATUS")) AS "LATEST_STATUS"
                     FROM "LEAD_ACT" a
                     JOIN (
                         SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                         FROM "LEAD_ACT"
                         GROUP BY "LEADID"
                     ) m ON m."LEADID" = a."LEADID" AND m.MAXCD = a."CREATIONDATE"
                 ) ls ON ls."LEADID" = l."LEADID"
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL AND TRIM(CAST(l."ASSIGNEDTO" AS VARCHAR(50))) <> \'\'
                   AND COALESCE(ls."LATEST_STATUS", \'\') <> \'CANCELLED\'
                 GROUP BY TRIM(CAST(l."ASSIGNEDTO" AS VARCHAR(50)))'
            );

            foreach ($rows as $row) {
                $uid = trim((string) ($row->UID ?? $row->uid ?? ''));
                if ($uid !== '' && isset($stats[$uid])) {
                    $stats[$uid] = [
                        'totalLead' => (int) ($row->TOTAL_LEAD ?? $row->total_lead ?? 0),
                        'totalClosed' => (int) ($row->TOTAL_CLOSED ?? $row->total_closed ?? 0),
                        'totalFailed' => (int) ($row->TOTAL_FAILED ?? $row->total_failed ?? 0),
                        'totalOngoing' => (int) ($row->TOTAL_ONGOING ?? $row->total_ongoing ?? 0),
                    ];
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Bulk dealer stats aggregation failed: '.$e->getMessage());
        }

        return $stats;
    }

    /**
     * Get average closing time for dealers
     * Calculates first PENDING to the latest COMPLETED/REWARDED time per lead
     */
    public static function getAllDealerClosingTimes(array $dealerIds = []): array
    {
        if (empty($dealerIds)) {
            return [];
        }

        $times = [];

        try {
            $rows = DB::select(
                'SELECT
                    l."ASSIGNEDTO" AS "assignedTo",
                    la."LEADID",
                    MIN(CASE WHEN UPPER(TRIM(la."STATUS")) = \'PENDING\' THEN la."CREATIONDATE" END) AS "PENDING_AT",
                    MIN(CASE WHEN UPPER(TRIM(la."STATUS")) IN (\'COMPLETED\', \'REWARDED\') THEN la."CREATIONDATE" END) AS "COMPLETED_AT"
                 FROM "LEAD" l
                 JOIN "LEAD_ACT" la ON la."LEADID" = l."LEADID"
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                 GROUP BY l."ASSIGNEDTO", la."LEADID"'
            );

            $dealerDurations = [];

            foreach ($rows as $row) {
                $dealer = trim((string) ($row->assignedTo ?? $row->ASSIGNEDTO ?? ''));
                $pendingAt = $row->PENDING_AT ?? $row->pending_at ?? null;
                $completedAt = $row->COMPLETED_AT ?? $row->completed_at ?? null;

                if ($dealer === '' || ! $pendingAt || ! $completedAt) {
                    continue;
                }

                $pendingTs = strtotime((string) $pendingAt);
                $completedTs = strtotime((string) $completedAt);

                if (! $pendingTs || ! $completedTs || $completedTs < $pendingTs) {
                    continue;
                }

                $duration = $completedTs - $pendingTs;

                if (! isset($dealerDurations[$dealer])) {
                    $dealerDurations[$dealer] = [];
                }

                $dealerDurations[$dealer][] = $duration;
            }

            // Calculate averages per dealer
            foreach ($dealerDurations as $dealer => $durations) {
                if (! empty($durations)) {
                    $times[$dealer] = (int) round(array_sum($durations) / count($durations));
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Closing time calculation failed: '.$e->getMessage());
        }

        return $times;
    }

    /**
     * Format display string from raw seconds
     */
    public static function formatClosingTime(?int $seconds): string
    {
        if ($seconds === null || $seconds <= 0) {
            return '-';
        }

        $mins = (int) floor($seconds / 60);

        if ($mins < 60) {
            return $mins.' min';
        }

        if ($mins < 60 * 24) {
            $h = (int) floor($mins / 60);
            $m = $mins % 60;

            return $h.'h '.$m.'m';
        }

        $d2 = (int) floor($mins / (60 * 24));
        $remM = $mins % (60 * 24);
        $h2 = (int) floor($remM / 60);

        return $d2.'d '.$h2.'h';
    }
}
