<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * High-performance dealer statistics aggregation
 * Replaces multiple N+1 queries with single GROUP BY
 */
class DealerStatsAggregator
{
    public static function closingTimeStartStatuses(): array
    {
        return [
            'PENDING',
        ];
    }

    public static function closingTimeCompletedStatuses(): array
    {
        return [
            'COMPLETED',
            'REWARDED',
        ];
    }

    public static function closingTimeStartStatusSql(): string
    {
        return self::toSqlStringList(self::closingTimeStartStatuses());
    }

    public static function closingTimeCompletedStatusSql(): string
    {
        return self::toSqlStringList(self::closingTimeCompletedStatuses());
    }

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
            if ($userId === '') {
                continue;
            }

            try {
                $leadsRow = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD" WHERE COALESCE("ISDELETED", FALSE) = FALSE AND TRIM(CAST("ASSIGNEDTO" AS VARCHAR(50))) = TRIM(CAST(? AS VARCHAR(50)))',
                    [$userId]
                );
                $closedRow = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD" l
                     WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
                       AND TRIM(CAST(l."ASSIGNEDTO" AS VARCHAR(50))) = TRIM(CAST(? AS VARCHAR(50)))
                       AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                            FROM "LEAD_ACT" la
                            WHERE la."LEADID" = l."LEADID"
                            ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                           ) IN (\'COMPLETED\', \'REWARDED\')',
                    [$userId]
                );
                $failedRow = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD" l
                     WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
                       AND TRIM(CAST(l."ASSIGNEDTO" AS VARCHAR(50))) = TRIM(CAST(? AS VARCHAR(50)))
                       AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                            FROM "LEAD_ACT" la
                            WHERE la."LEADID" = l."LEADID"
                            ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                           ) = \'FAILED\'',
                    [$userId]
                );
                $ongoingRow = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD" l
                     WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
                       AND TRIM(CAST(l."ASSIGNEDTO" AS VARCHAR(50))) = TRIM(CAST(? AS VARCHAR(50)))
                       AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                            FROM "LEAD_ACT" la
                            WHERE la."LEADID" = l."LEADID"
                            ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                           ) IN (\'PENDING\', \'FOLLOWUP\', \'FOLLOW UP\', \'DEMO\', \'CONFIRMED\')',
                    [$userId]
                );

                $stats[$userId] = [
                    'totalLead' => (int) ($leadsRow->c ?? $leadsRow->C ?? current((array) $leadsRow) ?? 0),
                    'totalClosed' => (int) ($closedRow->c ?? $closedRow->C ?? current((array) $closedRow) ?? 0),
                    'totalFailed' => (int) ($failedRow->c ?? $failedRow->C ?? current((array) $failedRow) ?? 0),
                    'totalOngoing' => (int) ($ongoingRow->c ?? $ongoingRow->C ?? current((array) $ongoingRow) ?? 0),
                ];
            } catch (\Throwable $e) {
                // If query fails, set zeros
                $stats[$userId] = [
                    'totalLead' => 0,
                    'totalClosed' => 0,
                    'totalFailed' => 0,
                    'totalOngoing' => 0,
                ];
            }
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
            // Get all LEAD_ACT records with status transitions
            $allRows = DB::select(
                'SELECT
                    a."LEADID",
                    a."STATUS",
                    a."CREATIONDATE",
                    l."ASSIGNEDTO" AS "assignedTo"
                 FROM "LEAD_ACT" a
                 LEFT JOIN "LEAD" l ON l."LEADID" = a."LEADID"
                 WHERE a."STATUS" IS NOT NULL
                 ORDER BY a."LEADID", a."CREATIONDATE"'
            );

            // Group by lead and extract start/end dates
            $leadTimings = [];
            
            foreach ($allRows as $row) {
                $leadId = (int) ($row->LEADID ?? 0);
                $status = strtoupper(trim((string) ($row->STATUS ?? '')));
                $createdAt = $row->CREATIONDATE;
                $assignedTo = trim((string) ($row->assignedTo ?? ''));

                if ($leadId <= 0 || !$createdAt || !$status) {
                    continue;
                }

                if (!isset($leadTimings[$leadId])) {
                    $leadTimings[$leadId] = [
                        'dealer' => $assignedTo,
                        'pending_at' => null,
                        'completed_at' => null,
                    ];
                }

                // Capture first PENDING
                if ($status === 'PENDING' && !$leadTimings[$leadId]['pending_at']) {
                    $leadTimings[$leadId]['pending_at'] = $createdAt;
                }

                // Capture first COMPLETED or REWARDED
                if (($status === 'COMPLETED' || $status === 'REWARDED') && !$leadTimings[$leadId]['completed_at']) {
                    $leadTimings[$leadId]['completed_at'] = $createdAt;
                }
            }

            // Calculate durations per dealer
            $dealerDurations = [];

            foreach ($leadTimings as $leadId => $timing) {
                $dealer = trim((string) ($timing['dealer'] ?? ''));
                $pendingAt = $timing['pending_at'];
                $completedAt = $timing['completed_at'];

                if (!$dealer || !$pendingAt || !$completedAt) {
                    continue;
                }

                $pendingTs = strtotime((string) $pendingAt);
                $completedTs = strtotime((string) $completedAt);

                if (!$pendingTs || !$completedTs || $completedTs < $pendingTs) {
                    continue;
                }

                $duration = $completedTs - $pendingTs;

                if (!isset($dealerDurations[$dealer])) {
                    $dealerDurations[$dealer] = [];
                }

                $dealerDurations[$dealer][] = $duration;
            }

            // Calculate averages per dealer
            foreach ($dealerDurations as $dealer => $durations) {
                if (!empty($durations)) {
                    $avgSeconds = (int) round(array_sum($durations) / count($durations));
                    $times[$dealer] = $avgSeconds;
                }
            }
        } catch (\Throwable $e) {
            // Return empty if query fails
            \Log::error('Closing time calculation failed: ' . $e->getMessage());
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
            return $mins . ' min';
        }
        
        if ($mins < 60 * 24) {
            $h = (int) floor($mins / 60);
            $m = $mins % 60;
            return $h . 'h ' . $m . 'm';
        }
        
        $d2 = (int) floor($mins / (60 * 24));
        $remM = $mins % (60 * 24);
        $h2 = (int) floor($remM / 60);
        return $d2 . 'd ' . $h2 . 'h';
    }

    private static function toSqlStringList(array $values): string
    {
        return implode(', ', array_map(function ($value) {
            return '\'' . str_replace('\'', '\'\'', strtoupper(trim((string) $value))) . '\'';
        }, $values));
    }
}
