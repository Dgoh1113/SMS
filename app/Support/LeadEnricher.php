<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Helper for enriching lead records with additional data from LEAD_ACT and USERS tables.
 * Consolidates the pattern of fetching and attaching:
 * - Latest LEAD_ACT status
 * - Completed/Rewarded/DealtProduct dates
 * - Attachment information with URLs
 */
class LeadEnricher
{
    /**
     * Enrich lead rows with:
     * - Latest LEAD_ACT status
     * - Completed/Rewarded/DealtProduct dates
     * - Attachment information
     * - User display names
     */
    public static function enrichLeads(array $rows, string $serveRoute, string $activityRoute): array
    {
        if (empty($rows)) {
            return $rows;
        }

        // Extract lead IDs
        $leadIds = [];
        foreach ($rows as $r) {
            $lid = StringHelper::toInteger($r->LEADID ?? 0);
            if ($lid > 0) {
                $leadIds[$lid] = true;
            }
        }
        $leadIds = array_keys($leadIds);

        if (empty($leadIds)) {
            return $rows;
        }

        // Enrich with latest LEAD_ACT status
        self::enrichWithLatestStatus($rows, $leadIds);

        // Enrich with completed/rewarded dates and dealt product
        self::enrichWithActivityDates($rows, $leadIds);

        // Enrich with attachment information
        self::enrichWithAttachments($rows, $leadIds, $serveRoute, $activityRoute);

        // Enrich with user display names
        self::enrichWithUserNames($rows);

        return $rows;
    }

    private static function enrichWithLatestStatus(array &$rows, array $leadIds): void
    {
        $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
        $acts = DB::select(
            "SELECT a.\"LEADID\", a.\"STATUS\"
             FROM \"LEAD_ACT\" a
             JOIN (
                 SELECT \"LEADID\", MAX(\"CREATIONDATE\") AS MAXCD
                 FROM \"LEAD_ACT\"
                 WHERE \"LEADID\" IN ($placeholders)
                 GROUP BY \"LEADID\"
             ) x ON x.\"LEADID\" = a.\"LEADID\" AND x.MAXCD = a.\"CREATIONDATE\"
             WHERE a.\"LEADID\" IN ($placeholders)",
            array_merge($leadIds, $leadIds)
        );

        $statusMap = [];
        foreach ($acts as $a) {
            $lid = StringHelper::toInteger($a->LEADID ?? 0);
            if ($lid > 0) {
                $statusMap[$lid] = StringHelper::normalize($a->STATUS ?? '');
            }
        }

        foreach ($rows as $r) {
            $lid = StringHelper::toInteger($r->LEADID ?? 0);
            if ($lid > 0 && isset($statusMap[$lid]) && $statusMap[$lid] !== '') {
                $r->CURRENTSTATUS = $statusMap[$lid];
            }
        }
    }

    private static function enrichWithActivityDates(array &$rows, array $leadIds): void
    {
        $placeholders = implode(',', array_fill(0, count($leadIds), '?'));

        // Get completed, rewarded dates and dealt product
        $completedRows = DB::select(
            "SELECT \"LEADID\", MAX(\"CREATIONDATE\") AS COMPLETED_AT
             FROM \"LEAD_ACT\"
             WHERE UPPER(TRIM(\"STATUS\")) = 'COMPLETED' AND \"LEADID\" IN ($placeholders)
             GROUP BY \"LEADID\"",
            $leadIds
        );

        $rewardedRows = DB::select(
            "SELECT \"LEADID\", MAX(\"CREATIONDATE\") AS REWARDED_AT
             FROM \"LEAD_ACT\"
             WHERE UPPER(TRIM(\"STATUS\")) = 'REWARDED' AND \"LEADID\" IN ($placeholders)
             GROUP BY \"LEADID\"",
            $leadIds
        );

        $dealRows = DB::select(
            "SELECT a.\"LEADID\", a.\"DEALTPRODUCT\"
             FROM \"LEAD_ACT\" a
             JOIN (
                 SELECT \"LEADID\", MAX(\"CREATIONDATE\") AS MAXCD
                 FROM \"LEAD_ACT\"
                 WHERE UPPER(TRIM(\"STATUS\")) = 'COMPLETED' AND \"LEADID\" IN ($placeholders)
                 GROUP BY \"LEADID\"
             ) m ON m.\"LEADID\" = a.\"LEADID\" AND m.MAXCD = a.\"CREATIONDATE\"
             WHERE UPPER(TRIM(a.\"STATUS\")) = 'COMPLETED' AND a.\"LEADID\" IN ($placeholders)",
            array_merge($leadIds, $leadIds)
        );

        $maps = ['completed' => [], 'rewarded' => [], 'dealt' => []];

        foreach ($completedRows as $item) {
            $lid = (int) ($item->LEADID ?? 0);
            if ($lid > 0) {
                $maps['completed'][$lid] = $item->COMPLETED_AT ?? $item->completed_at ?? null;
            }
        }

        foreach ($rewardedRows as $item) {
            $lid = (int) ($item->LEADID ?? 0);
            if ($lid > 0) {
                $maps['rewarded'][$lid] = $item->REWARDED_AT ?? $item->rewarded_at ?? null;
            }
        }

        foreach ($dealRows as $item) {
            $lid = (int) ($item->LEADID ?? 0);
            if ($lid > 0) {
                $maps['dealt'][$lid] = $item->DEALTPRODUCT ?? $item->dealtproduct ?? null;
            }
        }

        foreach ($rows as $r) {
            $lid = (int) ($r->LEADID ?? 0);
            if ($lid > 0) {
                if (isset($maps['completed'][$lid])) {
                    $r->COMPLETED_AT = $maps['completed'][$lid];
                }
                if (isset($maps['rewarded'][$lid])) {
                    $r->REWARDED_AT = $maps['rewarded'][$lid];
                }
                if (isset($maps['dealt'][$lid])) {
                    $r->DEALTPRODUCT = $maps['dealt'][$lid];
                }
            }
        }
    }

    private static function enrichWithAttachments(array &$rows, array $leadIds, string $serveRoute, string $activityRoute): void
    {
        $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
        $attachRows = DB::select(
            "SELECT \"LEADID\", \"LEAD_ACTID\", \"ATTACHMENT\", \"CREATIONDATE\"
             FROM \"LEAD_ACT\"
             WHERE \"LEADID\" IN ($placeholders) AND \"ATTACHMENT\" IS NOT NULL
             ORDER BY \"LEADID\" ASC, \"CREATIONDATE\" DESC, \"LEAD_ACTID\" DESC",
            $leadIds
        );

        $attachmentMap = [];
        foreach ($attachRows as $item) {
            $lid = (int) ($item->LEADID ?? 0);
            if ($lid <= 0 || array_key_exists($lid, $attachmentMap)) {
                continue;
            }
            $attachment = $item->ATTACHMENT ?? $item->attachment ?? null;
            if ($attachment === null || trim((string) $attachment) === '') {
                continue;
            }
            $attachmentMap[$lid] = [
                'attachment' => $attachment,
                'lead_act_id' => (int) ($item->LEAD_ACTID ?? 0),
            ];
        }

        foreach ($rows as $r) {
            $lid = (int) ($r->LEADID ?? 0);
            if ($lid > 0 && array_key_exists($lid, $attachmentMap)) {
                $r->ASSIGNED_ATTACHMENT = $attachmentMap[$lid]['attachment'];
                $r->ASSIGNED_LEAD_ACT_ID = $attachmentMap[$lid]['lead_act_id'];

                // Build URLs
                $urls = AttachmentUrlBuilder::buildUrls(
                    $attachmentMap[$lid]['attachment'],
                    $lid,
                    $attachmentMap[$lid]['lead_act_id'],
                    $serveRoute,
                    $activityRoute
                );
                $r->ASSIGNED_ATTACHMENT_URLS = $urls;
            }
        }
    }

    private static function enrichWithUserNames(array &$rows): void
    {
        // User enrichment is handled separately by the controller
        // This is left as a placeholder for potential future consolidation
    }
}
