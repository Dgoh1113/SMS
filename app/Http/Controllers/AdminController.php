<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DatabaseAggregates;
use App\Http\Controllers\Concerns\ResolvesInquiryAttachments;
use App\Http\Controllers\Concerns\UsesSetupLinkStore;
use App\Mail\InquiryAssignedToDealer;
use App\Mail\PayoutCompletedNotification;
use App\Mail\UserPasskeySetupLink;
use App\Support\AppConstants;
use App\Support\AttachmentUrlBuilder;
use App\Support\DealerStatsAggregator;
use App\Support\LeadEnricher;
use App\Support\ProductConstants;
use App\Support\QueryCache;
use App\Support\StringHelper;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminController extends Controller
{
    use DatabaseAggregates;
    use ResolvesInquiryAttachments;
    use UsesSetupLinkStore;

    private function buildDealerItems(): array
    {
        $rows = DB::select(
            'SELECT "USERID","EMAIL","POSTCODE","CITY","ISACTIVE","COMPANY","ALIAS"
             FROM "USERS"
             WHERE TRIM("SYSTEMROLE") = ?
             ORDER BY "USERID"',
            [AppConstants::ROLE_DEALER]
        );

        $leadStats = [];
        try {
            $statsRows = DB::select(
                'SELECT
                    TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50))) AS UID,
                    COUNT(*) AS TOTAL_LEAD,
                    SUM(CASE WHEN UPPER(TRIM("CURRENTSTATUS")) = ? THEN 1 ELSE 0 END) AS TOTAL_ONGOING,
                    SUM(CASE WHEN "CURRENTSTATUS" = \'Closed\' THEN 1 ELSE 0 END) AS TOTAL_CLOSED,
                    SUM(CASE WHEN UPPER(TRIM("CURRENTSTATUS")) = ? THEN 1 ELSE 0 END) AS TOTAL_FAILED
                 FROM "LEAD"
                 WHERE "ASSIGNED_TO" IS NOT NULL AND TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50))) <> \'\'
                 GROUP BY TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50)))',
                [AppConstants::STATUS_ONGOING, AppConstants::STATUS_FAILED]
            );

            foreach ($statsRows as $sr) {
                $uid = StringHelper::normalize($sr->UID ?? $sr->uid ?? '');
                if ($uid === '') {
                    continue;
                }

                $leadStats[$uid] = [
                    'totalLead' => StringHelper::toInteger($sr->TOTAL_LEAD ?? $sr->total_lead ?? ''),
                    'totalOngoing' => StringHelper::toInteger($sr->TOTAL_ONGOING ?? $sr->total_ongoing ?? ''),
                    'totalClosed' => StringHelper::toInteger($sr->TOTAL_CLOSED ?? $sr->total_closed ?? ''),
                    'totalFailed' => StringHelper::toInteger($sr->TOTAL_FAILED ?? $sr->total_failed ?? ''),
                ];
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to aggregate dealer stats: ' . $e->getMessage());
            // Leave stats empty if aggregation fails
        }

        return array_map(function ($r) use ($leadStats) {
            $uid = StringHelper::normalize($r->USERID ?? '');
            $stats = $leadStats[$uid] ?? [];
            $totalLead = $stats['totalLead'] ?? 0;
            $totalOngoing = $stats['totalOngoing'] ?? 0;
            $totalClosed = $stats['totalClosed'] ?? 0;
            $totalFailed = $stats['totalFailed'] ?? 0;
            $conversion = $totalLead > 0 ? ($totalClosed / $totalLead) * 100 : 0;

            $r->TOTAL_LEAD = $totalLead;
            $r->TOTAL_ONGOING = $totalOngoing;
            $r->TOTAL_CLOSED = $totalClosed;
            $r->TOTAL_FAILED = $totalFailed;
            $r->CONVERSION_RATE = $conversion;

            return $r;
        }, $rows);
    }

    private function loadInquiryPostcodeCityLookup(): array
    {
        return QueryCache::remember(AppConstants::CACHE_KEY_POSTCODE_LOOKUP, function () {
            static $lookup = null;

            if (is_array($lookup)) {
                return $lookup;
            }

            $lookup = [];
            $path = base_path('malaysia-postcodes.json');
            if (!is_file($path)) {
                return $lookup;
            }

            try {
                $decoded = json_decode((string) file_get_contents($path), true);
            } catch (\Throwable $e) {
                \Log::warning('Failed to load postcode lookup file: ' . $e->getMessage());
                return $lookup;
            }

            if (!is_array($decoded) || !isset($decoded['state']) || !is_array($decoded['state'])) {
                return $lookup;
            }

            foreach ($decoded['state'] as $state) {
                if (!is_array($state)) {
                    continue;
                }

                foreach (($state['city'] ?? []) as $city) {
                    if (!is_array($city)) {
                        continue;
                    }

                    $cityName = StringHelper::normalize($city['name'] ?? '');
                    if ($cityName === '') {
                        continue;
                    }

                    foreach (($city['postcode'] ?? []) as $postcode) {
                        $normalizedPostcode = StringHelper::digitsOnly((string) $postcode);
                        if (strlen($normalizedPostcode) !== 5 || isset($lookup[$normalizedPostcode])) {
                            continue;
                        }

                        $lookup[$normalizedPostcode] = $cityName;
                    }
                }
            }

            return $lookup;
        });
    }

    private function inquiryFormViewData(?object $inquiry = null): array
    {
        $dealers = [];
        try {
            $dealers = DB::select(
                'SELECT "USERID", "COMPANY", "EMAIL" FROM "USERS" WHERE UPPER(TRIM("SYSTEMROLE")) LIKE \'%DEALER%\' ORDER BY "COMPANY"'
            );
        } catch (\Throwable $e) {
            try {
                $dealers = DB::select(
                    'SELECT "USERID", "EMAIL" FROM "USERS" WHERE UPPER(TRIM("SYSTEMROLE")) LIKE \'%DEALER%\' ORDER BY "USERID"'
                );
            } catch (\Throwable $e2) {
                // leave empty
            }
        }

        $data = [
            'dealers' => $dealers,
            'productInterestedList' => ProductConstants::fullNames(),
            'postcodeCityLookup' => $this->loadInquiryPostcodeCityLookup(),
            'currentPage' => 'inquiries',
        ];

        if ($inquiry !== null) {
            $data['inquiry'] = $inquiry;
        }

        return $data;
    }

    private function latestAssignmentUserMap(array $leadIds): array
    {
        $leadIds = array_values(array_unique(array_filter(array_map('intval', $leadIds), static fn ($id) => $id > 0)));
        if (empty($leadIds)) {
            return [];
        }

        $cacheKey = AppConstants::CACHE_KEY_LATEST_ASSIGNMENT . md5(implode(',', $leadIds));
        
        return QueryCache::remember($cacheKey, function () use ($leadIds) {
            $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
            $rows = DB::select(
                'SELECT "LEADID", "LEAD_ACTID", "USERID", "DESCRIPTION"
                 FROM "LEAD_ACT"
                 WHERE "LEADID" IN (' . $placeholders . ')
                   AND (
                       UPPER(TRIM(COALESCE("SUBJECT", \'\'))) STARTING WITH ?
                       OR UPPER(TRIM(COALESCE("DESCRIPTION", \'\'))) STARTING WITH ?
                   )
                 ORDER BY "LEADID" ASC, "CREATIONDATE" DESC, "LEAD_ACTID" DESC',
                array_merge($leadIds, [AppConstants::ACTIVITY_STATUS_LEAD_ASSIGNED, AppConstants::ACTIVITY_STATUS_LEAD_ASSIGNED])
            );

            $map = [];
            foreach ($rows as $row) {
                $leadId = StringHelper::toInteger($row->LEADID ?? 0);
                if ($leadId <= 0 || array_key_exists($leadId, $map)) {
                    continue;
                }

                $userId = StringHelper::normalize($row->USERID ?? '');
                if ($userId === '') {
                    $desc = StringHelper::normalize($row->DESCRIPTION ?? '');
                    if ($desc !== '' && preg_match('/Lead Assigned by\s+(\S+)\s+to\s+(\S+)/i', $desc, $m)) {
                        $userId = StringHelper::normalize($m[1] ?? '');
                    }
                }

                if ($userId !== '') {
                    $map[$leadId] = $userId;
                }
            }

            return $map;
        });
    }

    private function userDisplayMaps(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(array_map(
            static fn ($id) => StringHelper::normalize($id),
            $userIds
        ), static fn ($id) => $id !== '')));

        if (empty($userIds)) {
            return ['assignedToMap' => [], 'actorMap' => []];
        }

        $cacheKey = AppConstants::CACHE_KEY_USER_DISPLAY_MAPS . md5(implode(',', $userIds));
        
        return QueryCache::remember($cacheKey, function () use ($userIds) {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $users = DB::select(
                'SELECT "USERID","SYSTEMROLE","ALIAS","COMPANY","EMAIL"
                 FROM "USERS"
                 WHERE CAST("USERID" AS VARCHAR(50)) IN (' . $placeholders . ')',
                $userIds
            );

            $assignedToMap = [];
            $actorMap = [];

            foreach ($users as $u) {
                $uid = StringHelper::normalize($u->USERID ?? '');
                if ($uid === '') {
                    continue;
                }

                $assignedToMap[$uid] = StringHelper::buildUserDisplayName($u, AppConstants::SEPARATOR_DISPLAY);
                $actorMap[$uid] = StringHelper::buildUserActorName($u, AppConstants::SEPARATOR_DISPLAY);
            }

            return ['assignedToMap' => $assignedToMap, 'actorMap' => $actorMap];
        });
    }

    private function getLeadCurrentActionState(int $leadId): ?array
    {
        $lead = DB::selectOne(
            'SELECT "LEADID","ASSIGNED_TO","CURRENTSTATUS" FROM "LEAD" WHERE "LEADID" = ?',
            [$leadId]
        );

        if (!$lead) {
            return null;
        }

        $latest = DB::selectOne(
            'SELECT FIRST 1 "STATUS"
             FROM "LEAD_ACT"
             WHERE "LEADID" = ?
             ORDER BY "CREATIONDATE" DESC, "LEAD_ACTID" DESC',
            [$leadId]
        );

        $assignedTo = StringHelper::normalize($lead->ASSIGNED_TO ?? '');
        $leadStatus = strtoupper(StringHelper::normalize($lead->CURRENTSTATUS ?? ''));
        $latestStatus = strtoupper(StringHelper::normalize($latest->STATUS ?? ''));

        return [
            'assigned_to' => $assignedTo,
            'status' => $assignedTo === ''
                ? ($leadStatus !== '' ? $leadStatus : $latestStatus)
                : $latestStatus,
        ];
    }

    private function incomingInquiryStaleMessage(int $leadId): ?string
    {
        $state = $this->getLeadCurrentActionState($leadId);
        if ($state === null) {
            return AppConstants::ERR_INQUIRY_NOT_FOUND;
        }

        $assignedTo = StringHelper::normalize($state['assigned_to'] ?? '');
        if ($assignedTo !== '') {
            $maps = $this->userDisplayMaps([$assignedTo]);
            $assignedToMap = $maps['assignedToMap'] ?? [];
            $assignedLabel = $assignedToMap[$assignedTo] ?? $assignedTo;

            return sprintf(AppConstants::ERR_INQUIRY_ALREADY_ASSIGNED, $assignedLabel);
        }

        $status = strtoupper(StringHelper::normalize($state['status'] ?? ''));
        if ($status !== '' && !in_array($status, [AppConstants::STATUS_OPEN, AppConstants::STATUS_CREATED], true)) {
            return sprintf(AppConstants::ERR_INQUIRY_ALREADY_PROCESSED, $status);
        }

        return null;
    }

    private function buildDashboardRollingStatusSeries(int $days, string $status): array
    {
        $days = max(1, $days);
        $normalizedStatus = strtoupper(trim($status));
        $end = Carbon::now()->endOfDay();
        $currentStart = Carbon::now()->subDays($days - 1)->startOfDay();
        $previousEnd = $currentStart->copy()->subSecond();
        $previousStart = $currentStart->copy()->subDays($days)->startOfDay();

        $rows = DB::select(
            'SELECT CAST("CREATIONDATE" AS DATE) AS day_key, COUNT(*) AS c
             FROM "LEAD_ACT"
             WHERE "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?
               AND UPPER(TRIM("STATUS")) = ?
             GROUP BY CAST("CREATIONDATE" AS DATE)
             ORDER BY CAST("CREATIONDATE" AS DATE)',
            [
                $currentStart->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s'),
                $normalizedStatus,
            ]
        );

        $countsByDay = [];
        foreach ($rows as $row) {
            $rawDay = $row->DAY_KEY ?? $row->day_key ?? null;
            if (!$rawDay) {
                continue;
            }

            try {
                $dayKey = Carbon::parse((string) $rawDay)->format('Y-m-d');
            } catch (\Throwable $e) {
                continue;
            }

            $countsByDay[$dayKey] = (int) ($row->C ?? $row->c ?? current((array) $row) ?? 0);
        }

        $labels = [];
        $tooltipTitles = [];
        $data = [];
        $currentTotal = 0;

        for ($offset = 0; $offset < $days; $offset++) {
            $day = $currentStart->copy()->addDays($offset);
            $dayKey = $day->format('Y-m-d');
            $value = (int) ($countsByDay[$dayKey] ?? 0);

            $labels[] = $day->format('j M');
            $tooltipTitles[] = $day->format('D, j M Y');
            $data[] = $value;
            $currentTotal += $value;
        }

        $previousRow = DB::selectOne(
            'SELECT COUNT(*) AS c
             FROM "LEAD_ACT"
             WHERE "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?
               AND UPPER(TRIM("STATUS")) = ?',
            [
                $previousStart->format('Y-m-d H:i:s'),
                $previousEnd->format('Y-m-d H:i:s'),
                $normalizedStatus,
            ]
        );

        $previousTotal = (int) ($previousRow->C ?? $previousRow->c ?? current((array) $previousRow) ?? 0);
        $change = $previousTotal > 0
            ? round((($currentTotal - $previousTotal) / $previousTotal) * 100, 1)
            : ($currentTotal > 0 ? 100.0 : 0.0);

        if (abs($change) < 0.05) {
            $change = 0.0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'tooltipTitles' => $tooltipTitles,
            'change' => $change,
            'days' => $days,
        ];
    }

    private function dashboardData(): array
    {
        // Total leads: all rows in LEAD
        $leadCountRow = DB::selectOne('SELECT COUNT(*) as cnt FROM "LEAD"');
        $totalLeads = (int) ($leadCountRow->cnt ?? $leadCountRow->CNT ?? current((array) $leadCountRow) ?? 0);

        // Total closed: LEAD_ACT with STATUS = 'Completed'
        $closedRow = DB::selectOne(
            'SELECT COUNT(*) as cnt FROM "LEAD_ACT" WHERE UPPER(TRIM("STATUS")) = \'COMPLETED\''
        );
        $totalClosed = (int) ($closedRow->cnt ?? $closedRow->CNT ?? current((array) $closedRow) ?? 0);

        // Active inquiries: LEAD with CURRENTSTATUS = 'Ongoing'
        $activeRow = DB::selectOne(
            'SELECT COUNT(*) as cnt FROM "LEAD" WHERE "CURRENTSTATUS" = \'Ongoing\''
        );
        $activeInquiries = (int) ($activeRow->cnt ?? $activeRow->CNT ?? current((array) $activeRow) ?? 0);

        // Conversion rate: closed / total leads
        $conversionRate = $totalLeads > 0 ? round(($totalClosed / $totalLeads) * 100, 1) : 0;
        // Average closing time: from first PENDING to first COMPLETED
        $avgClosingSeconds = null;
        $avgClosingTime = '-';

        try {
            // Get all lead activity records
            $allRows = DB::select(
                'SELECT
                    a."LEADID",
                    a."STATUS",
                    a."CREATIONDATE"
                 FROM "LEAD_ACT" a
                 WHERE a."STATUS" IS NOT NULL
                 ORDER BY a."LEADID", a."CREATIONDATE"'
            );

            $leadTimings = [];
            
            foreach ($allRows as $row) {
                $leadId = (int) ($row->LEADID ?? 0);
                $status = strtoupper(trim((string) ($row->STATUS ?? '')));
                $createdAt = $row->CREATIONDATE;

                if ($leadId <= 0 || !$createdAt || !$status) {
                    continue;
                }

                if (!isset($leadTimings[$leadId])) {
                    $leadTimings[$leadId] = [
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

            // Calculate average across all leads
            $total = 0;
            $count = 0;

            foreach ($leadTimings as $timing) {
                $pendingAt = $timing['pending_at'];
                $completedAt = $timing['completed_at'];

                if (!$pendingAt || !$completedAt) {
                    continue;
                }

                $pendingTs = strtotime((string) $pendingAt);
                $completedTs = strtotime((string) $completedAt);

                if (!$pendingTs || !$completedTs || $completedTs < $pendingTs) {
                    continue;
                }

                $duration = $completedTs - $pendingTs;
                $total += $duration;
                $count++;
            }

            if ($count > 0) {
                $avgClosingSeconds = (int) round($total / $count);
            }
        } catch (\Throwable $e) {
            \Log::error('Dashboard closing time calculation failed: ' . $e->getMessage());
            $avgClosingSeconds = null;
        }

        $avgClosingTime = DealerStatsAggregator::formatClosingTime($avgClosingSeconds);

        // Week-over-week comparison uses the current week versus the previous week for activity cards.
        $startThisWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endLastWeek = $startThisWeek->copy()->subSecond();
        $startLastWeek = $startThisWeek->copy()->subWeek();
        $leadsThisWeek = 0;
        $leadsLastWeek = 0;
        $closedThisWeek = 0;
        $closedLastWeek = 0;
        $leadsUntilLastWeek = 0;
        $closedUntilLastWeek = 0;
        $referralThisWeek = 0;
        $referralLastWeek = 0;
        $activeThisWeek = $activeInquiries;
        $activeLastWeek = 0;
        $countActiveSnapshot = null;
        $percentChange = static function ($current, $previous): float {
            $change = $previous > 0
                ? round((($current - $previous) / $previous) * 100, 1)
                : ($current > 0 ? 100.0 : 0.0);

            return abs($change) < 0.05 ? 0.0 : $change;
        };
        try {
            $countActiveSnapshot = function (string $cutoff) {
                $row = DB::selectOne(
                    'SELECT COUNT(*) AS c
                     FROM "LEAD" l
                     LEFT JOIN (
                         SELECT a."LEADID", a."STATUS"
                         FROM "LEAD_ACT" a
                         JOIN (
                             SELECT "LEADID", MAX("CREATIONDATE") AS max_created
                             FROM "LEAD_ACT"
                             WHERE "CREATIONDATE" <= ?
                             GROUP BY "LEADID"
                         ) m ON m."LEADID" = a."LEADID" AND m.max_created = a."CREATIONDATE"
                     ) la ON la."LEADID" = l."LEADID"
                     WHERE l."CREATEDAT" <= ?
                       AND (
                           UPPER(TRIM(COALESCE(la."STATUS", \'\'))) IN (\'PENDING\', \'FOLLOWUP\', \'DEMO\', \'CONFIRMED\')
                           OR (
                               TRIM(COALESCE(la."STATUS", \'\')) = \'\'
                               AND UPPER(TRIM(COALESCE(l."CURRENTSTATUS", \'\'))) = \'ONGOING\'
                           )
                       )',
                    [$cutoff, $cutoff]
                );

                return (int) ($row->c ?? $row->C ?? 0);
            };

            $r = DB::selectOne(
                'SELECT COUNT(*) AS c FROM "LEAD" WHERE "CREATEDAT" >= ? AND "CREATEDAT" <= ?',
                [$startThisWeek->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d 23:59:59')]
            );
            $leadsThisWeek = (int) ($r->c ?? $r->C ?? 0);
            $r = DB::selectOne(
                'SELECT COUNT(*) AS c FROM "LEAD" WHERE "CREATEDAT" >= ? AND "CREATEDAT" <= ?',
                [$startLastWeek->format('Y-m-d H:i:s'), $endLastWeek->format('Y-m-d 23:59:59')]
            );
            $leadsLastWeek = (int) ($r->c ?? $r->C ?? 0);

            $r = DB::selectOne(
                'SELECT COUNT(*) AS c FROM "LEAD_ACT" WHERE UPPER(TRIM("STATUS")) = \'COMPLETED\' AND "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?',
                [$startThisWeek->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d 23:59:59')]
            );
            $closedThisWeek = (int) ($r->c ?? $r->C ?? 0);
            $r = DB::selectOne(
                'SELECT COUNT(*) AS c FROM "LEAD_ACT" WHERE UPPER(TRIM("STATUS")) = \'COMPLETED\' AND "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?',
                [$startLastWeek->format('Y-m-d H:i:s'), $endLastWeek->format('Y-m-d 23:59:59')]
            );
            $closedLastWeek = (int) ($r->c ?? $r->C ?? 0);

            $r = DB::selectOne(
                'SELECT COUNT(*) AS c FROM "LEAD" WHERE "CREATEDAT" <= ?',
                [$endLastWeek->format('Y-m-d H:i:s')]
            );
            $leadsUntilLastWeek = (int) ($r->c ?? $r->C ?? 0);

            $r = DB::selectOne(
                'SELECT COUNT(*) AS c FROM "LEAD_ACT" WHERE UPPER(TRIM("STATUS")) = \'COMPLETED\' AND "CREATIONDATE" <= ?',
                [$endLastWeek->format('Y-m-d H:i:s')]
            );
            $closedUntilLastWeek = (int) ($r->c ?? $r->C ?? 0);

            $r = DB::selectOne(
                'SELECT COUNT(*) AS c FROM "LEAD_ACT" WHERE "STATUS" = \'FollowUp\' AND "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?',
                [$startThisWeek->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d 23:59:59')]
            );
            $referralThisWeek = (int) ($r->c ?? $r->C ?? 0);
            $r = DB::selectOne(
                'SELECT COUNT(*) AS c FROM "LEAD_ACT" WHERE "STATUS" = \'FollowUp\' AND "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?',
                [$startLastWeek->format('Y-m-d H:i:s'), $endLastWeek->format('Y-m-d 23:59:59')]
            );
            $referralLastWeek = (int) ($r->c ?? $r->C ?? 0);

            $activeThisWeek = $activeInquiries;
            $activeLastWeek = $countActiveSnapshot($endLastWeek->format('Y-m-d H:i:s'));
        } catch (\Throwable $e) {
            $activeThisWeek = $activeInquiries;
            $activeLastWeek = 0;
        }

        $pctLeads = $percentChange($leadsThisWeek, $leadsLastWeek);
        $pctClosed = $percentChange($closedThisWeek, $closedLastWeek);
        $pctActive = $percentChange($activeThisWeek, $activeLastWeek);
        $pctReferral = $percentChange($referralThisWeek, $referralLastWeek);
        $conversionRateLastWeek = $leadsUntilLastWeek > 0 ? ($closedUntilLastWeek / $leadsUntilLastWeek) * 100 : 0;
        $conversionRateChange = round($conversionRate - $conversionRateLastWeek, 1);
        if (abs($conversionRateChange) < 0.05) {
            $conversionRateChange = 0.0;
        }

        $dashboardMetricRangeChanges = [];
        foreach ([30, 60, 90] as $rollingDays) {
            $currentStart = Carbon::now()->subDays($rollingDays - 1)->startOfDay();
            $currentEnd = Carbon::now()->endOfDay();
            $previousEnd = $currentStart->copy()->subSecond();
            $previousStart = $currentStart->copy()->subDays($rollingDays)->startOfDay();

            $rangeLeadsCurrent = 0;
            $rangeLeadsPrevious = 0;
            $rangeClosedCurrent = 0;
            $rangeClosedPrevious = 0;
            $rangeActivePrevious = 0;

            try {
                $r = DB::selectOne(
                    'SELECT COUNT(*) AS c FROM "LEAD" WHERE "CREATEDAT" >= ? AND "CREATEDAT" <= ?',
                    [$currentStart->format('Y-m-d H:i:s'), $currentEnd->format('Y-m-d H:i:s')]
                );
                $rangeLeadsCurrent = (int) ($r->c ?? $r->C ?? 0);

                $r = DB::selectOne(
                    'SELECT COUNT(*) AS c FROM "LEAD" WHERE "CREATEDAT" >= ? AND "CREATEDAT" <= ?',
                    [$previousStart->format('Y-m-d H:i:s'), $previousEnd->format('Y-m-d H:i:s')]
                );
                $rangeLeadsPrevious = (int) ($r->c ?? $r->C ?? 0);

                $r = DB::selectOne(
                    'SELECT COUNT(*) AS c FROM "LEAD_ACT" WHERE UPPER(TRIM("STATUS")) = \'COMPLETED\' AND "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?',
                    [$currentStart->format('Y-m-d H:i:s'), $currentEnd->format('Y-m-d H:i:s')]
                );
                $rangeClosedCurrent = (int) ($r->c ?? $r->C ?? 0);

                $r = DB::selectOne(
                    'SELECT COUNT(*) AS c FROM "LEAD_ACT" WHERE UPPER(TRIM("STATUS")) = \'COMPLETED\' AND "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?',
                    [$previousStart->format('Y-m-d H:i:s'), $previousEnd->format('Y-m-d H:i:s')]
                );
                $rangeClosedPrevious = (int) ($r->c ?? $r->C ?? 0);

                if (is_callable($countActiveSnapshot)) {
                    $rangeActivePrevious = (int) $countActiveSnapshot($previousEnd->format('Y-m-d H:i:s'));
                }
            } catch (\Throwable $e) {
                $rangeLeadsCurrent = 0;
                $rangeLeadsPrevious = 0;
                $rangeClosedCurrent = 0;
                $rangeClosedPrevious = 0;
                $rangeActivePrevious = 0;
            }

            $currentPeriodConversion = $rangeLeadsCurrent > 0 ? ($rangeClosedCurrent / $rangeLeadsCurrent) * 100 : 0;
            $previousPeriodConversion = $rangeLeadsPrevious > 0 ? ($rangeClosedPrevious / $rangeLeadsPrevious) * 100 : 0;
            $rangeConversionChange = round($currentPeriodConversion - $previousPeriodConversion, 1);
            if (abs($rangeConversionChange) < 0.05) {
                $rangeConversionChange = 0.0;
            }

            $dashboardMetricRangeChanges[(string) $rollingDays] = [
                'leads' => $percentChange($rangeLeadsCurrent, $rangeLeadsPrevious),
                'closed' => $percentChange($rangeClosedCurrent, $rangeClosedPrevious),
                'active' => $percentChange($activeInquiries, $rangeActivePrevious),
                'conversion' => $rangeConversionChange,
            ];
        }

        if (isset($dashboardMetricRangeChanges['30'])) {
            $pctLeads = (float) ($dashboardMetricRangeChanges['30']['leads'] ?? $pctLeads);
            $pctClosed = (float) ($dashboardMetricRangeChanges['30']['closed'] ?? $pctClosed);
            $pctActive = (float) ($dashboardMetricRangeChanges['30']['active'] ?? $pctActive);
            $conversionRateChange = (float) ($dashboardMetricRangeChanges['30']['conversion'] ?? $conversionRateChange);
        }

        $dealerStats = [];
        try {
            // Top Active Dealers (USERS + LEAD) per requested logic:
            // Leads: COUNT(*) from LEAD where ASSIGNED_TO = dealer
            // Closed: COUNT(*) where ASSIGNED_TO = dealer and CURRENTSTATUS = 'Closed'
            // Conversion: Closed / Leads
            // Pull dealer list first so we can build a readable company/alias label.
            // Older schemas may not expose every profile column; still return rows.
            $topDealersRaw = [];
        try {
            $topDealersRaw = DB::select(
                    'SELECT u."USERID", u."EMAIL", u."COMPANY" AS "COMPANY", u."ALIAS" AS "ALIAS", u."POSTCODE" AS "POSTCODE", u."CITY" AS "CITY"
                FROM "USERS" u
                     WHERE UPPER(TRIM(u."SYSTEMROLE")) LIKE \'%DEALER%\''
                );
            } catch (\Throwable $e) {
                try {
                    $topDealersRaw = DB::select(
                        'SELECT u."USERID", u."EMAIL", u."COMPANY" AS "COMPANY", \'\' AS "ALIAS", u."POSTCODE" AS "POSTCODE", u."CITY" AS "CITY"
                         FROM "USERS" u
                         WHERE UPPER(TRIM(u."SYSTEMROLE")) LIKE \'%DEALER%\''
                    );
                } catch (\Throwable $e) {
                    $topDealersRaw = DB::select(
                        'SELECT u."USERID", u."EMAIL", \'\' AS "COMPANY", \'\' AS "ALIAS", \'\' AS "POSTCODE", \'\' AS "CITY"
                         FROM "USERS" u
                         WHERE UPPER(TRIM(u."SYSTEMROLE")) LIKE \'%DEALER%\''
                    );
                }
            }

            // PERFORMANCE: Get dealer statistics with proper dealer ID extraction
            $dealerIds = array_map(fn($d) => (string) ($d->USERID ?? ''), $topDealersRaw);
            $allStats = DealerStatsAggregator::getAllDealerStats($dealerIds);
            $allClosingTimes = DealerStatsAggregator::getAllDealerClosingTimes($dealerIds);

            $dealerStats = collect($topDealersRaw)->map(function ($d) use ($allStats, $allClosingTimes) {
                $userId = (string) ($d->USERID ?? '');
                
                // Get pre-fetched stats for this dealer (eliminates N+1)
                $stats = $allStats[$userId] ?? ['totalLead' => 0, 'totalClosed' => 0, 'totalFailed' => 0, 'totalOngoing' => 0];
                $leads = $stats['totalLead'] ?? 0;
                $closed = $stats['totalClosed'] ?? 0;
                $failed = $stats['totalFailed'] ?? 0;
                $ongoing = $stats['totalOngoing'] ?? 0;
                $conversion = $leads > 0 ? ($closed / $leads) : 0;

                // Get pre-fetched closing time (eliminates N+1)
                $avgClosingSeconds = $allClosingTimes[$userId] ?? null;

                $company = trim((string) ($d->COMPANY ?? ''));
                $alias = trim((string) ($d->ALIAS ?? ''));
                $email = trim((string) ($d->EMAIL ?? ''));
                $dealerName = $company !== '' ? $company : ($alias !== '' ? $alias : ($email !== '' ? $email : $userId));
                if (strcasecmp($company, 'E Stream Sdn Bhd') === 0 && $alias !== '') {
                    $dealerName = $company . '-' . $alias;
                }

                $avgClosingDisplay = DealerStatsAggregator::formatClosingTime($avgClosingSeconds);

                $postcode = trim((string) ($d->POSTCODE ?? ''));
                $city = trim((string) ($d->CITY ?? ''));
                $location = trim(trim($postcode . ' ' . $city));

                return [
                    'dealer_name' => $dealerName,
                    'location' => $location,
                    'total_leads' => $leads,
                    'ongoing_count' => $ongoing,
                    'closed_count' => $closed,
                    'failed_count' => $failed,
                    'conversion_rate' => round($conversion * 100, 1),
                    'avg_closing_time' => $avgClosingDisplay,
                    'avg_closing_seconds' => $avgClosingSeconds,
                ];
            })
                ->sort(function (array $a, array $b) {
                    $c = ($b['conversion_rate'] <=> $a['conversion_rate']);
                    if ($c !== 0) return $c;
                    $ta = $a['avg_closing_seconds'] ?? null;
                    $tb = $b['avg_closing_seconds'] ?? null;
                    $hasA = is_int($ta);
                    $hasB = is_int($tb);
                    if ($hasA && $hasB) {
                        $c2 = ($ta <=> $tb);
                        if ($c2 !== 0) return $c2;
                    } elseif ($hasA !== $hasB) {
                        return $hasA ? -1 : 1;
                    }
                    $c3 = ($b['closed_count'] <=> $a['closed_count']);
                    if ($c3 !== 0) return $c3;
                    return ($b['total_leads'] <=> $a['total_leads']);
                })
                ->values()
                ->all();
        } catch (\Throwable $e) {
            // Schema may differ; keep empty
        }

        $dashboardClosedCaseRanges = [];
        $dashboardReferralRanges = [];
        $dashboardReferralRangeChanges = [];
        foreach ([30, 60, 90] as $rollingDays) {
            try {
                $closedSeries = $this->buildDashboardRollingStatusSeries($rollingDays, 'COMPLETED');
                $referralSeries = $this->buildDashboardRollingStatusSeries($rollingDays, 'FOLLOWUP');
            } catch (\Throwable $e) {
                $labels = [];
                $tooltipTitles = [];
                for ($offset = $rollingDays - 1; $offset >= 0; $offset--) {
                    $day = Carbon::now()->subDays($offset);
                    $labels[] = $day->format('j M');
                    $tooltipTitles[] = $day->format('D, j M Y');
                }

                $closedSeries = [
                    'labels' => $labels,
                    'data' => array_fill(0, $rollingDays, 0),
                    'tooltipTitles' => $tooltipTitles,
                    'change' => 0.0,
                ];
                $referralSeries = [
                    'labels' => $labels,
                    'data' => array_fill(0, $rollingDays, 0),
                    'tooltipTitles' => $tooltipTitles,
                    'change' => 0.0,
                ];
            }

            $rangeKey = (string) $rollingDays;
            $dashboardClosedCaseRanges[$rangeKey] = [
                'labels' => $closedSeries['labels'],
                'data' => $closedSeries['data'],
                'tooltipTitles' => $closedSeries['tooltipTitles'],
            ];
            $dashboardReferralRanges[$rangeKey] = [
                'labels' => $referralSeries['labels'],
                'data' => $referralSeries['data'],
                'tooltipTitles' => $referralSeries['tooltipTitles'],
            ];
            $dashboardReferralRangeChanges[$rangeKey] = (float) ($referralSeries['change'] ?? 0.0);
        }

        return [
            'totalLeads' => $totalLeads,
            'totalClosed' => $totalClosed,
            'activeInquiries' => $activeInquiries,
            'conversionRate' => $conversionRate,
            'avgClosingTime' => $avgClosingTime,
            'avgClosingSeconds' => $avgClosingSeconds,
            'pctLeads' => $pctLeads,
            'pctClosed' => $pctClosed,
            'pctActive' => $pctActive,
            'conversionRateChange' => $conversionRateChange,
            'pctReferral' => $pctReferral,
            'dashboardMetricRangeChanges' => $dashboardMetricRangeChanges,
            'topDealers' => $dealerStats,
            'dashboardClosedCaseRanges' => $dashboardClosedCaseRanges,
            'dashboardReferralRanges' => $dashboardReferralRanges,
            'dashboardReferralRangeChanges' => $dashboardReferralRangeChanges,
        ];
    }

    public function dashboard(): View
    {
        return view('admin.dashboard', array_merge($this->dashboardData(), ['currentPage' => 'dashboard']));
    }

    public function inquiries(): View
    {
        $rows = DB::select(
            'SELECT FIRST 200
                "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL","ADDRESS1","ADDRESS2","CITY","POSTCODE",
                "BUSINESSNATURE","USERCOUNT","EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                "CURRENTSTATUS","CREATEDAT","CREATEDBY","ASSIGNED_TO","LASTMODIFIED"
            FROM "LEAD"
            ORDER BY "LEADID" DESC'
        );
        foreach ($rows as $r) {
            $r->LEAD_CURRENTSTATUS = trim((string) ($r->CURRENTSTATUS ?? ''));
        }

        // Override CURRENTSTATUS from latest LEAD_ACT status per LEADID
        try {
            $leadIds = [];
            foreach ($rows as $r) {
                $lid = (int)($r->LEADID ?? 0);
                if ($lid > 0) {
                    $leadIds[$lid] = true;
                }
            }
            $leadIds = array_keys($leadIds);
            if (!empty($leadIds)) {
                $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
                // Get latest LEAD_ACT row per LEADID by CREATIONDATE
                $acts = DB::select(
                    'SELECT a."LEADID", a."STATUS"
                     FROM "LEAD_ACT" a
                     JOIN (
                         SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                         FROM "LEAD_ACT"
                         WHERE "LEADID" IN (' . $placeholders . ')
                         GROUP BY "LEADID"
                     ) x
                       ON x."LEADID" = a."LEADID" AND x.MAXCD = a."CREATIONDATE"
                     WHERE a."LEADID" IN (' . $placeholders . ')',
                    array_merge($leadIds, $leadIds)
                );
                $statusMap = [];
                foreach ($acts as $a) {
                    $lid = (int)($a->LEADID ?? 0);
                    if ($lid > 0) {
                        $statusMap[$lid] = trim((string)($a->STATUS ?? ''));
                    }
                }
                if (!empty($statusMap)) {
                    foreach ($rows as $r) {
                        $lid = (int)($r->LEADID ?? 0);
                        if ($lid > 0 && isset($statusMap[$lid]) && $statusMap[$lid] !== '') {
                            $r->CURRENTSTATUS = $statusMap[$lid];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // If LEAD_ACT lookup fails, keep CURRENTSTATUS from LEAD
        }

        $unassigned = [];
        $assigned = [];
        foreach ($rows as $r) {
            $assignedTo = trim((string) ($r->ASSIGNED_TO ?? ''));
            $leadStatus = strtoupper(trim((string) ($r->LEAD_CURRENTSTATUS ?? $r->CURRENTSTATUS ?? '')));
            if ($assignedTo === '' && in_array($leadStatus, ['OPEN', 'CREATED'], true)) {
                $r->CURRENTSTATUS = $r->LEAD_CURRENTSTATUS ?? $r->CURRENTSTATUS;
                $unassigned[] = $r;
            } elseif ($assignedTo !== '') {
                $assigned[] = $r;
            }
        }

        // Sort unassigned and assigned
        usort($unassigned, function ($a, $b) {
            $ta = strtotime($a->CREATEDAT ?? '0');
            $tb = strtotime($b->CREATEDAT ?? '0');
            return $ta <=> $tb;
        });
        usort($assigned, function ($a, $b) {
            $ta = strtotime($a->LASTMODIFIED ?? $a->CREATEDAT ?? '0');
            $tb = strtotime($b->LASTMODIFIED ?? $b->CREATEDAT ?? '0');
            return $tb <=> $ta;
        });

        // Enrich leads with activity dates, dealt products, and attachments
        $rows = LeadEnricher::enrichLeads(
            $rows,
            'admin.rewards.serve-attachment',
            'admin.rewards.activity-attachment'
        );

        // Resolve display names for source, assigned by, and assigned to.
        try {
            $assignmentByLeadMap = $this->latestAssignmentUserMap(array_map(
                static fn ($row) => (int) ($row->LEADID ?? 0),
                $rows
            ));
            $ids = [];
            foreach ($rows as $r) {
                $to = trim((string) ($r->ASSIGNED_TO ?? ''));
                $by = trim((string) ($r->CREATEDBY ?? ''));
                if ($to !== '') $ids[$to] = true;
                if ($by !== '') $ids[$by] = true;
                $leadId = (int) ($r->LEADID ?? 0);
                $assignerId = $leadId > 0 ? trim((string) ($assignmentByLeadMap[$leadId] ?? '')) : '';
                if ($assignerId !== '') $ids[$assignerId] = true;
            }
            $maps = $this->userDisplayMaps(array_keys($ids));
            $assignedToMap = $maps['assignedToMap'];
            $actorMap = $maps['actorMap'];
            if (!empty($assignedToMap) || !empty($actorMap)) {
                foreach ($rows as $r) {
                    $to = trim((string) ($r->ASSIGNED_TO ?? ''));
                    $by = trim((string) ($r->CREATEDBY ?? ''));
                    $leadId = (int) ($r->LEADID ?? 0);
                    $assignerId = $leadId > 0 ? trim((string) ($assignmentByLeadMap[$leadId] ?? '')) : '';
                    if ($to !== '' && isset($assignedToMap[$to])) $r->ASSIGNED_TO_NAME = $assignedToMap[$to];
                    if ($by !== '' && isset($actorMap[$by])) $r->CREATEDBY_NAME = $actorMap[$by];
                    if ($assignerId !== '') $r->ASSIGNEDBY = $assignerId;
                    if ($assignerId !== '' && isset($actorMap[$assignerId])) {
                        $r->ASSIGNEDBY_NAME = $actorMap[$assignerId];
                    }
                }
            }
        } catch (\Throwable $e) {
            // fall back to raw ids
        }
        $totalNewInquiries = count($unassigned);
        // Assigned badge count: assigned inquiries whose latest status is not closed/rewarded/failed.
        $totalOngoing = 0;
        foreach ($assigned as $r) {
            $status = strtoupper(trim((string) ($r->CURRENTSTATUS ?? '')));
            if (!in_array($status, ['COMPLETED', 'CASE COMPLETED', 'FAILED', 'REWARDED', 'REWARD', 'REWARD DISTRIBUTED'], true)) {
                $totalOngoing++;
            }
        }

        // Use product labels from constants
        $productLabels = ProductConstants::all();

        // Dealer list for Assign dropdown: only active dealers (with stats similar to Dealers page)
        $dealers = [];
        try {
            $baseDealers = DB::select(
                'SELECT "USERID","EMAIL","POSTCODE","CITY","ISACTIVE","COMPANY","ALIAS"
                 FROM "USERS"
                 WHERE TRIM("SYSTEMROLE") = \'Dealer\'
                   AND "ISACTIVE" = TRUE
                 ORDER BY "COMPANY"'
            );

            $leadStats = [];
            try {
                $statsRows = DB::select(
                    'SELECT
                        TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50))) AS UID,
                        COUNT(*) AS TOTAL_LEAD,
                        SUM(CASE WHEN "CURRENTSTATUS" = \'Closed\' THEN 1 ELSE 0 END) AS TOTAL_CLOSED
                     FROM "LEAD"
                     WHERE "ASSIGNED_TO" IS NOT NULL AND TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50))) <> \'\'
                     GROUP BY TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50)))'
                );
                foreach ($statsRows as $sr) {
                    $uid = trim((string)($sr->UID ?? $sr->uid ?? ''));
                    if ($uid === '') continue;
                    $totalLead = (int)($sr->TOTAL_LEAD ?? $sr->total_lead ?? 0);
                    $totalClosed = (int)($sr->TOTAL_CLOSED ?? $sr->total_closed ?? 0);
                    $leadStats[$uid] = [
                        'totalLead' => $totalLead,
                        'totalClosed' => $totalClosed,
                    ];
                }
            } catch (\Throwable $e) {
                // leave stats empty
            }

            $dealers = array_map(function ($r) use ($leadStats) {
                $uid = trim((string)($r->USERID ?? ''));
                $totalLead = $leadStats[$uid]['totalLead'] ?? 0;
                $totalClosed = $leadStats[$uid]['totalClosed'] ?? 0;
                $conversion = $totalLead > 0 ? ($totalClosed / $totalLead) * 100 : 0;
                $r->TOTAL_LEAD = $totalLead;
                $r->TOTAL_CLOSED = $totalClosed;
                $r->CONVERSION_RATE = $conversion;
                return $r;
            }, $baseDealers);
        } catch (\Throwable $e) {
            // leave empty
        }

        $assignedPerPage = 10;
        $assignedTotal = count($assigned);
        $assignedForView = $assigned;
        $assignedLastPage = $assignedTotal > 0 ? (int) ceil($assignedTotal / $assignedPerPage) : 1;
        $allRows = $rows;
        $allPerPage = 10;
        $allTotal = count($allRows);

        return view('admin.inquiries', [
            'unassigned' => $unassigned,
            'assigned' => $assignedForView,
            'assignedTotal' => $assignedTotal,
            'assignedPerPage' => $assignedPerPage,
            'assignedCurrentPage' => 1,
            'assignedLastPage' => $assignedLastPage,
            'allRows' => $allRows,
            'allTotal' => $allTotal,
            'allPerPage' => $allPerPage,
            'totalNewInquiries' => $totalNewInquiries,
            'totalOngoing' => $totalOngoing,
            'productLabels' => $productLabels,
            'dealers' => $dealers,
            'currentPage' => 'inquiries',
        ]);
    }

    public function inquiriesAssignedPage(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 10;

        $rows = DB::select(
            'SELECT FIRST 200
                "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL","ADDRESS1","ADDRESS2","CITY","POSTCODE",
                "BUSINESSNATURE","USERCOUNT","EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                "CURRENTSTATUS","CREATEDAT","CREATEDBY","ASSIGNED_TO","LASTMODIFIED"
            FROM "LEAD"
            ORDER BY "LEADID" DESC'
        );
        $assigned = [];
        foreach ($rows as $r) {
            if (trim((string) ($r->ASSIGNED_TO ?? '')) !== '') {
                $assigned[] = $r;
            }
        }
        usort($assigned, function ($a, $b) {
            $ta = strtotime($a->LASTMODIFIED ?? $a->CREATEDAT ?? '0');
            $tb = strtotime($b->LASTMODIFIED ?? $b->CREATEDAT ?? '0');
            return $tb <=> $ta;
        });

        $leadIds = array_values(array_unique(array_filter(array_map(function ($r) { return (int)($r->LEADID ?? 0); }, $rows))));
        if (!empty($leadIds)) {
            $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
            try {
                $acts = DB::select(
                    'SELECT a."LEADID", a."STATUS"
                     FROM "LEAD_ACT" a
                     JOIN (
                         SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                         FROM "LEAD_ACT"
                         WHERE "LEADID" IN (' . $placeholders . ')
                         GROUP BY "LEADID"
                     ) x
                       ON x."LEADID" = a."LEADID" AND x.MAXCD = a."CREATIONDATE"
                     WHERE a."LEADID" IN (' . $placeholders . ')',
                    array_merge($leadIds, $leadIds)
                );
                $statusMap = [];
                foreach ($acts as $a) {
                    $lid = (int)($a->LEADID ?? 0);
                    if ($lid > 0) $statusMap[$lid] = trim((string)($a->STATUS ?? ''));
                }
                foreach ($rows as $r) {
                    $lid = (int)($r->LEADID ?? 0);
                    if ($lid > 0 && isset($statusMap[$lid]) && $statusMap[$lid] !== '') {
                        $r->CURRENTSTATUS = $statusMap[$lid];
                    }
                }
            } catch (\Throwable $e) {
                // keep CURRENTSTATUS from LEAD
            }
        }

        // Enrich leads with activity dates, dealt products, and attachments
        $rows = LeadEnricher::enrichLeads(
            $rows,
            'admin.rewards.serve-attachment',
            'admin.rewards.activity-attachment'
        );

        try {
            $assignmentByLeadMap = $this->latestAssignmentUserMap(array_map(
                static fn ($row) => (int) ($row->LEADID ?? 0),
                $rows
            ));
            $ids = [];
            foreach ($rows as $r) {
                $to = trim((string) ($r->ASSIGNED_TO ?? ''));
                $by = trim((string) ($r->CREATEDBY ?? ''));
                if ($to !== '') $ids[$to] = true;
                if ($by !== '') $ids[$by] = true;
                $leadId = (int) ($r->LEADID ?? 0);
                $assignerId = $leadId > 0 ? trim((string) ($assignmentByLeadMap[$leadId] ?? '')) : '';
                if ($assignerId !== '') $ids[$assignerId] = true;
            }
            $maps = $this->userDisplayMaps(array_keys($ids));
            $assignedToMap = $maps['assignedToMap'];
            $actorMap = $maps['actorMap'];
            foreach ($rows as $r) {
                $to = trim((string) ($r->ASSIGNED_TO ?? ''));
                $by = trim((string) ($r->CREATEDBY ?? ''));
                $leadId = (int) ($r->LEADID ?? 0);
                $assignerId = $leadId > 0 ? trim((string) ($assignmentByLeadMap[$leadId] ?? '')) : '';
                if ($to !== '' && isset($assignedToMap[$to])) $r->ASSIGNED_TO_NAME = $assignedToMap[$to];
                if ($by !== '' && isset($actorMap[$by])) $r->CREATEDBY_NAME = $actorMap[$by];
                if ($assignerId !== '') $r->ASSIGNEDBY = $assignerId;
                if ($assignerId !== '' && isset($actorMap[$assignerId])) {
                    $r->ASSIGNEDBY_NAME = $actorMap[$assignerId];
                }
            }
        } catch (\Throwable $e) {
            // fallback raw ids
        }

        $productLabels = ProductConstants::all();
        $assignedTotal = count($assigned);
        $assignedLastPage = $assignedTotal > 0 ? (int) ceil($assignedTotal / $perPage) : 1;
        $page = min($page, $assignedLastPage);
        $offset = ($page - 1) * $perPage;
        $assignedSlice = array_slice($assigned, $offset, $perPage);

        $html = view('admin.partials.inquiries_assigned_rows', [
            'assigned' => $assignedSlice,
            'productLabels' => $productLabels,
        ])->render();

        return response()->json([
            'html' => $html,
            'assignedTotal' => $assignedTotal,
            'assignedPerPage' => $perPage,
            'currentPage' => $page,
            'lastPage' => $assignedLastPage,
        ]);
    }

    public function inquiriesSync(): JsonResponse
    {
        // Reuse the same data as the main inquiries page
        $view = $this->inquiries();
        $data = $view->getData();

        $unassignedHtml = view('admin.partials.inquiries_unassigned_rows', $data)->render();
        $assignedHtml = view('admin.partials.inquiries_assigned_rows', $data)->render();
        $allHtml = view('admin.partials.inquiries_all_rows', $data)->render();

        return response()->json([
            'unassigned' => $unassignedHtml,
            'assigned' => $assignedHtml,
            'all' => $allHtml,
            'totalNewInquiries' => $data['totalNewInquiries'] ?? 0,
            'totalOngoing' => $data['totalOngoing'] ?? 0,
            'assignedTotal' => $data['assignedTotal'] ?? 0,
            'assignedLastPage' => $data['assignedLastPage'] ?? 1,
            'allTotal' => $data['allTotal'] ?? 0,
        ]);
    }

    public function assignInquiry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'LEADID' => 'required',
            'ASSIGNED_TO' => 'required',
        ]);

        $leadId = (int) $validated['LEADID'];
        $assignedTo = trim((string) $validated['ASSIGNED_TO']);
        $fromUserId = trim((string) ($request->session()->get('user_id') ?? ''));

        if ($leadId <= 0 || $assignedTo === '') {
            return back()->with('error', 'Invalid assignment request.');
        }

        // Ensure assignee is an active dealer
        try {
            $assignee = DB::selectOne(
                'SELECT "USERID","SYSTEMROLE","ISACTIVE" FROM "USERS" WHERE CAST("USERID" AS VARCHAR(50)) = ?',
                [$assignedTo]
            );
            if (!$assignee) {
                return back()->with('error', 'Selected user not found.');
            }
            if (trim((string) ($assignee->SYSTEMROLE ?? '')) !== 'Dealer') {
                return back()->with('error', 'Lead can only be assigned to a dealer.');
            }
            $isActive = $assignee->ISACTIVE ?? false;
            if ($isActive !== true && $isActive !== 1 && $isActive !== '1') {
                return back()->with('error', 'Lead can only be assigned to an active dealer.');
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not verify assignee.');
        }

        // Remember previous assignee for possible undo
        $prevAssignedTo = null;
        $prevLastModified = null;
        try {
            $current = DB::selectOne(
                'SELECT "ASSIGNED_TO", "LASTMODIFIED" FROM "LEAD" WHERE "LEADID" = ?',
                [$leadId]
            );
            if ($current) {
                $prevAssignedTo = trim((string) ($current->ASSIGNED_TO ?? ''));
                $prevLastModified = $current->LASTMODIFIED ?? null;
            }
        } catch (\Throwable $e) {
            $prevAssignedTo = null;
            $prevLastModified = null;
        }

        try {
            DB::beginTransaction();
            // Keep DB context so database-side assignment logic/triggers can use the assigner id.
            if ($fromUserId !== '') {
                DB::statement(
                    "SELECT RDB\$SET_CONTEXT('USER_SESSION', 'ASSIGNER', ?) FROM RDB\$DATABASE",
                    [$fromUserId]
                );
            }
            $updated = DB::update(
                'UPDATE "LEAD"
                 SET "ASSIGNED_TO" = ?, "LASTMODIFIED" = CURRENT_TIMESTAMP
                 WHERE "LEADID" = ?
                   AND ("ASSIGNED_TO" IS NULL OR TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50))) = \'\')',
                [$assignedTo, $leadId]
            );
            if ((int) $updated < 1) {
                $currentAssignedRow = DB::selectOne(
                    'SELECT "ASSIGNED_TO" FROM "LEAD" WHERE "LEADID" = ?',
                    [$leadId]
                );
                DB::rollBack();

                $currentAssigned = trim((string) ($currentAssignedRow->ASSIGNED_TO ?? ''));
                if ($currentAssigned !== '') {
                    $maps = $this->userDisplayMaps([$currentAssigned]);
                    $assignedToMap = $maps['assignedToMap'] ?? [];
                    $currentAssignedLabel = $assignedToMap[$currentAssigned] ?? $currentAssigned;

                    return back()->with('error', 'This inquiry is already assigned to ' . $currentAssignedLabel . '. Please sync and try again.');
                }

                return back()->with('error', 'This inquiry was updated by another user. Please sync and try again.');
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Could not assign lead. Please try again.');
        }

        $undoPayload = [
            'lead_id' => $leadId,
            'prev_assigned_to' => $prevAssignedTo,
            'new_assigned_to' => $assignedTo,
            'prev_lastmodified' => $prevLastModified,
        ];

        return redirect()->route('admin.inquiries')
            ->with('success', 'Lead assigned successfully.')
            ->with('assign_undo', $undoPayload);
    }

    /**
     * Send dealer assignment email. Called by frontend 6 seconds after assign (after undo window).
     * Only sends if the lead is still assigned to the given dealer (undo was not clicked).
     */
    public function sendAssignmentEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => 'required|integer|min:1',
            'assigned_to' => 'required|string|max:50',
        ]);
        $leadId = (int) $validated['lead_id'];
        $assignedTo = trim((string) $validated['assigned_to']);

        $row = DB::selectOne('SELECT "ASSIGNED_TO" FROM "LEAD" WHERE "LEADID" = ?', [$leadId]);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
        }
        $currentAssigned = trim((string) ($row->ASSIGNED_TO ?? ''));
        if ($currentAssigned !== $assignedTo) {
            return response()->json(['success' => true, 'message' => 'Assignment was undone, email not sent.']);
        }

        $this->sendInquiryAssignedEmail($assignedTo, $leadId);
        return response()->json(['success' => true]);
    }

    public function undoAssignInquiry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'LEADID' => 'required|integer|min:1',
            'PREV_ASSIGNED_TO' => 'nullable|string|max:50',
            'PREV_LASTMODIFIED' => 'nullable|string|max:50',
        ]);
        $leadId = (int) $validated['LEADID'];
        $prev = trim((string) ($validated['PREV_ASSIGNED_TO'] ?? ''));
        $prevLastModified = trim((string) ($validated['PREV_LASTMODIFIED'] ?? ''));

        try {
            DB::beginTransaction();

            $assignedTo = $prev !== '' ? $prev : null;
            $restoredLastModified = $prevLastModified !== '' ? $prevLastModified : null;

            if ($prev === '') {
                $updated = DB::update(
                    'UPDATE "LEAD"
                     SET "ASSIGNED_TO" = ?, "CURRENTSTATUS" = ?, "LASTMODIFIED" = ?
                     WHERE "LEADID" = ?',
                    [$assignedTo, 'Open', $restoredLastModified, $leadId]
                );
            } else {
                $updated = DB::update(
                    'UPDATE "LEAD"
                     SET "ASSIGNED_TO" = ?, "LASTMODIFIED" = ?
                     WHERE "LEADID" = ?',
                    [$assignedTo, $restoredLastModified, $leadId]
                );
            }

            if ((int) $updated < 1) {
                DB::rollBack();
                return redirect()->route('admin.inquiries')->with('error', 'Lead not found.');
            }

              if ($prev === '') {
                  DB::delete(
                      'DELETE FROM "LEAD_ACT"
                       WHERE "LEADID" = ?
                         AND UPPER(TRIM(COALESCE("STATUS", \'\'))) = ?',
                      [$leadId, 'PENDING']
                  );

                  DB::update(
                      'UPDATE "LEAD"
                       SET "CURRENTSTATUS" = ?, "LASTMODIFIED" = ?
                       WHERE "LEADID" = ?',
                      ['Open', $restoredLastModified, $leadId]
                  );
              }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('admin.inquiries')->with('error', 'Could not undo assignment. Please try again.');
        }

        return redirect()->route('admin.inquiries')->with('success', 'Assignment undone.');
    }

    public function markInquiryFailed(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'LEADID' => 'required|integer|min:1',
            'FAIL_REASON' => 'nullable|string|max:4000|required_without:DESCRIPTION',
            'FAIL_DETAIL' => 'nullable|string|max:4000',
            'DESCRIPTION' => 'nullable|string|max:4000|required_without:FAIL_REASON',
        ]);
        $leadId = (int) $validated['LEADID'];
        $reason = trim((string) ($validated['FAIL_REASON'] ?? ''));
        $legacyDescription = trim((string) ($validated['DESCRIPTION'] ?? ''));
        $userId = trim((string) ($request->session()->get('user_id') ?? ''));

        $latest = DB::selectOne(
            'SELECT FIRST 1 "STATUS" FROM "LEAD_ACT" WHERE "LEADID" = ? ORDER BY "CREATIONDATE" DESC, "LEAD_ACTID" DESC',
            [$leadId]
        );
        $currentStatus = $latest ? strtoupper(trim((string) ($latest->STATUS ?? ''))) : '';
        if (in_array($currentStatus, ['COMPLETED', 'REWARDED', 'FAILED'], true)) {
            return back()->with('error', 'Cannot mark as Failed: lead is already ' . $currentStatus . '.');
        }

        if ($reason !== '') {
            $message = $reason;
        } else {
            $message = $legacyDescription;
        }

        try {
            DB::beginTransaction();
            DB::update(
                'UPDATE "LEAD" SET "LASTMODIFIED" = CURRENT_TIMESTAMP WHERE "LEADID" = ?',
                [$leadId]
            );
            DB::insert(
                'INSERT INTO "LEAD_ACT" ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS")
                 VALUES (NEXT VALUE FOR GEN_LEAD_ACTID,?,?,CURRENT_TIMESTAMP,?,?,?,?)',
                [$leadId, $userId !== '' ? $userId : null, 'Status changed to Failed', $message, null, 'Failed']
            );
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Could not mark the inquiry as failed. Please try again.');
        }

        return redirect()->route('admin.inquiries')->with('success', 'Lead marked as Failed.');
    }

    public function leadStatus(int $leadId): \Illuminate\Http\JsonResponse
    {
        $rows = DB::select(
            'SELECT "LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS"
             FROM "LEAD_ACT" WHERE "LEADID" = ? ORDER BY "CREATIONDATE" DESC, "LEAD_ACTID" DESC',
            [$leadId]
        );

        $userIds = [];
        foreach ($rows as $r) {
            $uid = trim((string) ($r->USERID ?? ''));
            if ($uid !== '') {
                $userIds[$uid] = true;
            }
        }

        $userNameMap = [];
        try {
            $ids = array_keys($userIds);
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $users = DB::select(
                    'SELECT "USERID","SYSTEMROLE","ALIAS","COMPANY","EMAIL"
                     FROM "USERS"
                     WHERE CAST("USERID" AS VARCHAR(50)) IN (' . $placeholders . ')',
                    $ids
                );
                foreach ($users as $u) {
                    $uid = trim((string) ($u->USERID ?? ''));
                    if ($uid === '') {
                        continue;
                    }
                    $role = trim((string) ($u->SYSTEMROLE ?? ''));
                    $alias = trim((string) ($u->ALIAS ?? ''));
                    $company = trim((string) ($u->COMPANY ?? ''));
                    $email = trim((string) ($u->EMAIL ?? ''));
                    $fallback = $email !== '' ? $email : $uid;

                    if ($role !== '' && $alias !== '') {
                        $userNameMap[$uid] = $role . '- ' . $alias;
                    } elseif ($role !== '') {
                        $userNameMap[$uid] = $role . '- ' . ($company !== '' ? $company : ($email !== '' ? $email : $uid));
                    } elseif ($alias !== '') {
                        $userNameMap[$uid] = $alias;
                    } else {
                        $userNameMap[$uid] = $fallback;
                    }
                }
            }
        } catch (\Throwable $e) {
            $userNameMap = [];
        }

        $activities = [];
        foreach ($rows as $r) {
            $createdAtIso = null;
            if (!empty($r->CREATIONDATE)) {
                try {
                    $createdAtIso = Carbon::parse($r->CREATIONDATE)->toIso8601String();
                } catch (\Throwable $e) {
                    $createdAtIso = (string) $r->CREATIONDATE;
                }
            }

            $status = trim((string) ($r->STATUS ?? ''));
            $userId = trim((string) ($r->USERID ?? ''));
            $activities[] = [
                'type' => strtoupper($status) === 'CREATED' ? 'created' : 'activity',
                'user' => $userId !== '' ? ($userNameMap[$userId] ?? $userId) : 'System',
                'subject' => trim((string) ($r->SUBJECT ?? '')),
                'description' => trim((string) ($r->DESCRIPTION ?? '')),
                'status' => $status,
                'created_at' => $createdAtIso,
                'attachment_urls' => AttachmentUrlBuilder::buildUrls(
                    $r->ATTACHMENT ?? null,
                    (int) ($r->LEADID ?? $leadId),
                    (int) ($r->LEAD_ACTID ?? 0),
                    'admin.rewards.serve-attachment',
                    'admin.rewards.activity-attachment'
                ),
            ];
        }

        $items = array_map(fn ($r) => [
            'LEAD_ACTID' => $r->LEAD_ACTID,
            'LEADID' => $r->LEADID,
            'USERID' => $r->USERID,
            'CREATIONDATE' => $r->CREATIONDATE,
            'SUBJECT' => $r->SUBJECT,
            'DESCRIPTION' => $r->DESCRIPTION,
            'STATUS' => $r->STATUS,
        ], $rows);

        return response()->json([
            'items' => $items,
            'activities' => $activities,
        ]);
    }

    public function companyLookup(Request $request): JsonResponse
    {
        $name = trim((string) $request->query('q', ''));
        if ($name === '') {
            return response()->json(['found' => false]);
        }

        try {
            $row = DB::selectOne(
                'SELECT FIRST 1
                    "LEADID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL",
                    "ADDRESS1","ADDRESS2","CITY","POSTCODE","BUSINESSNATURE",
                    "EXISTINGSOFTWARE","USERCOUNT","DEMOMODE"
                 FROM "LEAD"
                 WHERE UPPER(TRIM("COMPANYNAME")) = UPPER(TRIM(?))
                 ORDER BY "LEADID" DESC',
                [$name]
            );
            if (!$row) {
                return response()->json(['found' => false]);
            }

            return response()->json([
                'found' => true,
                'leadId' => (int) ($row->LEADID ?? 0),
                'companyname' => (string) ($row->COMPANYNAME ?? ''),
                'contactname' => (string) ($row->CONTACTNAME ?? ''),
                'contactno' => (string) ($row->CONTACTNO ?? ''),
                'email' => (string) ($row->EMAIL ?? ''),
                'address1' => (string) ($row->ADDRESS1 ?? ''),
                'address2' => (string) ($row->ADDRESS2 ?? ''),
                'city' => (string) ($row->CITY ?? ''),
                'postcode' => (string) ($row->POSTCODE ?? ''),
                'businessnature' => (string) ($row->BUSINESSNATURE ?? ''),
                'existingsoftware' => (string) ($row->EXISTINGSOFTWARE ?? ''),
                'usercount' => (string) ($row->USERCOUNT ?? ''),
                'demomode' => (string) ($row->DEMOMODE ?? ''),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['found' => false]);
        }
    }

    public function createInquiry(): View
    {
        return view('admin.inquiries-create', $this->inquiryFormViewData());
    }

    public function editInquiry(int $leadId): View|RedirectResponse
    {
        $staleMessage = $this->incomingInquiryStaleMessage($leadId);
        if ($staleMessage !== null && $staleMessage !== 'Lead not found.') {
            return redirect()->route('admin.inquiries')->with('error', $staleMessage);
        }

        $row = DB::selectOne(
            'SELECT "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL",
                "ADDRESS1","ADDRESS2","CITY","POSTCODE","BUSINESSNATURE","USERCOUNT",
                "EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                COALESCE("LASTMODIFIED", "CREATEDAT") AS "SNAPSHOT_MODIFIED_AT"
             FROM "LEAD" WHERE "LEADID" = ?',
            [$leadId]
        );
        if (!$row) {
            return redirect()->route('admin.inquiries')->with('error', 'Lead not found.');
        }

        return view('admin.inquiries-create', $this->inquiryFormViewData($row));
    }

    public function storeInquiry(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'COMPANYNAME' => 'required|string|max:255',
                'CONTACTNAME' => 'required|string|max:255',
                'CONTACTNO' => 'required|string|min:10|max:15',
                'EMAIL' => 'required|email|max:255',
                'ADDRESS1' => 'nullable|string|max:255',
                'ADDRESS2' => 'nullable|string|max:255',
                'CITY' => 'required|string|max:100',
                'POSTCODE' => 'required|string|digits:5',
                'BUSINESSNATURE' => 'required|string|max:255',
                'USERCOUNT' => 'nullable|string|max:50',
                'EXISTINGSOFTWARE' => 'required|string|max:255',
                'DEMOMODE' => 'required|string|in:Zoom,On-site',
                'product_interested' => 'required|array',
                'product_interested.*' => 'integer|in:1,2,3,4,5,6,7,8,9,10,11',
                'DESCRIPTION' => 'nullable|string|max:4000',
                'REFERRALCODE' => 'nullable|string|max:100',
                'ASSIGNED_TO' => 'nullable|string|max:50',
            ],
            [
                'CONTACTNO.min'          => 'Invalid Contact Number.',
                'CONTACTNO.max'          => 'Invalid Contact Number.',
                'POSTCODE.digits'        => 'Invalid PostCode.',
                'product_interested.*'   => 'Please select at least one product.',
                'product_interested.min' => 'Please select at least one product.',
                'product_interested.required' => 'Please select at least one product.',
            ],
            [
                'CONTACTNO' => 'Contact no',
                'POSTCODE'  => 'Post code',
            ]
        );

        // Soft-check for existing lead with the same company name (case-insensitive).
        // First submit: show a friendly warning; second submit with duplicate_ok=1: proceed.
        if (!$request->boolean('duplicate_ok')) {
            try {
                $existing = DB::selectOne(
                    'SELECT FIRST 1 "LEADID","COMPANYNAME","CONTACTNAME","EMAIL","CURRENTSTATUS","CREATEDAT"
                     FROM "LEAD"
                     WHERE UPPER(TRIM("COMPANYNAME")) = UPPER(TRIM(?))
                     ORDER BY
                        CASE
                            WHEN UPPER(TRIM(COALESCE("CURRENTSTATUS", \'\'))) = \'FAILED\' THEN 1
                            ELSE 0
                        END ASC,
                        COALESCE("LASTMODIFIED", "CREATEDAT") DESC,
                        "LEADID" DESC',
                    [$validated['COMPANYNAME']]
                );
                if ($existing) {
                    $status = strtoupper(trim((string) ($existing->CURRENTSTATUS ?? $existing->currentstatus ?? '')));
                    // If existing lead is Failed, accept without confirmation; otherwise show confirmation.
                    if ($status !== 'FAILED') {
                        $leadId = (int) ($existing->LEADID ?? 0);
                        $created = $existing->CREATEDAT ?? $existing->createdat ?? null;
                        $createdLabel = $created ? date('d/m/Y', strtotime((string) $created)) : null;

                        $line1 = 'This company already has an open inquiry.';
                        $parts = [];
                        if ($leadId > 0) {
                            $parts[] = 'Lead #SQL-' . $leadId;
                        }
                        if ($createdLabel) {
                            $parts[] = 'was created on ' . $createdLabel;
                        }
                        if ($status !== '') {
                            $parts[] = 'with status ' . $status;
                        }
                        $line2 = $parts ? implode(' ', $parts) . '.' : '';
                        $message = trim($line1 . "\n\n" . $line2);

                        return back()
                            ->withInput($request->except('duplicate_ok'))
                            ->with('duplicate_warning', $message);
                    }
                }
            } catch (\Throwable $e) {
                // If lookup fails, continue with normal flow.
            }
        }

        $userId = $request->session()->get('user_id');
        $productInterested = array_map('intval', $validated['product_interested']);
        $productInterested = array_unique(array_filter($productInterested));
        sort($productInterested, SORT_NUMERIC);
        $productIdValue = implode(',', $productInterested);
        $description = trim($validated['DESCRIPTION'] ?? '');
        $descriptionValue = $description !== '' ? $description : null;

        try {
            DB::insert(
                'INSERT INTO "LEAD" (
                    "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL",
                    "ADDRESS1","ADDRESS2","CITY","POSTCODE","BUSINESSNATURE","USERCOUNT",
                    "EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                    "CURRENTSTATUS","CREATEDAT","CREATEDBY","ASSIGNED_TO","LASTMODIFIED"
                ) VALUES (GEN_ID(GEN_LEADID, 1),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,?,?,CURRENT_TIMESTAMP)',
                [
                    $productIdValue,
                    $validated['COMPANYNAME'],
                    $validated['CONTACTNAME'],
                    $validated['CONTACTNO'],
                    $validated['EMAIL'],
                    $validated['ADDRESS1'] ?? null,
                    $validated['ADDRESS2'] ?? null,
                    $validated['CITY'],
                    $validated['POSTCODE'],
                    $validated['BUSINESSNATURE'],
                    $validated['USERCOUNT'] ?? null,
                    $validated['EXISTINGSOFTWARE'],
                    $validated['DEMOMODE'],
                    $descriptionValue,
                    $validated['REFERRALCODE'] ?? null,
                    'Open',
                    $userId,
                    $validated['ASSIGNED_TO'] ?? null,
                ]
            );
        } catch (\Throwable $e) {
            return back()->withInput($request->only(array_keys($validated)))->with('error', 'Could not save the inquiry. Please try again.');
        }

        $assignedTo = trim((string) ($validated['ASSIGNED_TO'] ?? ''));
        $assignEmailPending = null;
        if ($assignedTo !== '') {
            $newLeadIdRow = DB::selectOne('SELECT GEN_ID(GEN_LEADID, 0) AS "ID" FROM RDB$DATABASE');
            $newLeadId = (int) ($newLeadIdRow->ID ?? $newLeadIdRow->id ?? 0);
            if ($newLeadId > 0) {
                $assignEmailPending = ['lead_id' => $newLeadId, 'assigned_to' => $assignedTo];
            }
        }

        $redirect = redirect()->route('admin.inquiries')->with('success', 'Inquiry created.');
        if ($assignEmailPending !== null) {
            $redirect->with('assign_email_pending', $assignEmailPending);
        }
        return $redirect;
    }

    public function updateInquiry(Request $request, int $leadId): RedirectResponse
    {
        $validated = $request->validate(
            [
                'COMPANYNAME' => 'required|string|max:255',
                'CONTACTNAME' => 'required|string|max:255',
                'CONTACTNO' => 'required|string|min:10|max:15',
                'EMAIL' => 'required|email|max:255',
                'ADDRESS1' => 'nullable|string|max:255',
                'ADDRESS2' => 'nullable|string|max:255',
                'CITY' => 'required|string|max:100',
                'POSTCODE' => 'required|string|digits:5',
                'BUSINESSNATURE' => 'required|string|max:255',
                'USERCOUNT' => 'nullable|string|max:50',
                'EXISTINGSOFTWARE' => 'required|string|max:255',
                'DEMOMODE' => 'required|string|in:Zoom,On-site',
                'product_interested' => 'required|array',
                'product_interested.*' => 'integer|in:1,2,3,4,5,6,7,8,9,10,11',
                'DESCRIPTION' => 'nullable|string|max:4000',
                'REFERRALCODE' => 'nullable|string|max:100',
                'INQUIRY_SNAPSHOT_AT' => 'nullable|string|max:50',
            ],
            [
                'CONTACTNO.min'          => 'Invalid Contact Number.',
                'CONTACTNO.max'          => 'Invalid Contact Number.',
                'POSTCODE.digits'        => 'Invalid PostCode.',
                'product_interested.*'   => 'Please select at least one product.',
                'product_interested.required' => 'Please select at least one product.',
            ],
            [
                'CONTACTNO' => 'Contact no',
                'POSTCODE'  => 'Post code',
            ]
        );

        $exists = DB::selectOne('SELECT "LEADID" FROM "LEAD" WHERE "LEADID" = ?', [$leadId]);
        if (!$exists) {
            return redirect()->route('admin.inquiries')->with('error', 'Lead not found.');
        }

        $snapshotMessage = $this->inquiryEditSnapshotMessage($leadId, $request->input('INQUIRY_SNAPSHOT_AT'));
        if ($snapshotMessage !== null) {
            if ($snapshotMessage === 'Lead not found.') {
                return redirect()->route('admin.inquiries')->with('error', $snapshotMessage);
            }

            return redirect()->route('admin.inquiries.edit', $leadId)->with('error', $snapshotMessage);
        }

        $staleMessage = $this->incomingInquiryStaleMessage($leadId);
        if ($staleMessage !== null) {
            return redirect()->route('admin.inquiries')->with('error', $staleMessage);
        }

        // Same as create: if company name exists on another lead, show confirmation (exclude current lead)
        if (!$request->boolean('duplicate_ok')) {
            try {
                $existing = DB::selectOne(
                    'SELECT FIRST 1 "LEADID","COMPANYNAME","CONTACTNAME","EMAIL","CURRENTSTATUS","CREATEDAT"
                     FROM "LEAD"
                     WHERE UPPER(TRIM("COMPANYNAME")) = UPPER(TRIM(?)) AND "LEADID" <> ?
                     ORDER BY
                        CASE
                            WHEN UPPER(TRIM(COALESCE("CURRENTSTATUS", \'\'))) = \'FAILED\' THEN 1
                            ELSE 0
                        END ASC,
                        COALESCE("LASTMODIFIED", "CREATEDAT") DESC,
                        "LEADID" DESC',
                    [$validated['COMPANYNAME'], $leadId]
                );
                if ($existing) {
                    $status = strtoupper(trim((string) ($existing->CURRENTSTATUS ?? $existing->currentstatus ?? '')));
                    // If existing lead is Failed, accept without confirmation; otherwise show confirmation.
                    if ($status !== 'FAILED') {
                        $otherLeadId = (int) ($existing->LEADID ?? 0);
                        $created = $existing->CREATEDAT ?? $existing->createdat ?? null;
                        $createdLabel = $created ? date('d/m/Y', strtotime((string) $created)) : null;

                        $line1 = 'This company already has an open inquiry.';
                        $parts = [];
                        if ($otherLeadId > 0) {
                            $parts[] = 'Lead #SQL-' . $otherLeadId;
                        }
                        if ($createdLabel) {
                            $parts[] = 'was created on ' . $createdLabel;
                        }
                        if ($status !== '') {
                            $parts[] = 'with status ' . $status;
                        }
                        $line2 = $parts ? implode(' ', $parts) . '.' : '';
                        $message = trim($line1 . "\n\n" . $line2);

                        return redirect()
                            ->route('admin.inquiries.edit', $leadId)
                            ->withInput($request->except('duplicate_ok'))
                            ->with('duplicate_warning', $message);
                    }
                }
            } catch (\Throwable $e) {
                // If lookup fails, continue with normal flow.
            }
        }

        $productInterested = array_map('intval', $validated['product_interested']);
        $productInterested = array_unique(array_filter($productInterested));
        sort($productInterested, SORT_NUMERIC);
        $productIdValue = implode(',', $productInterested);
        $description = trim($validated['DESCRIPTION'] ?? '');
        $descriptionValue = $description !== '' ? $description : null;

        try {
            DB::update(
                'UPDATE "LEAD" SET
                    "PRODUCTID" = ?, "COMPANYNAME" = ?, "CONTACTNAME" = ?, "CONTACTNO" = ?, "EMAIL" = ?,
                    "ADDRESS1" = ?, "ADDRESS2" = ?, "CITY" = ?, "POSTCODE" = ?, "BUSINESSNATURE" = ?,
                    "USERCOUNT" = ?, "EXISTINGSOFTWARE" = ?, "DEMOMODE" = ?, "DESCRIPTION" = ?, "REFERRALCODE" = ?,
                    "LASTMODIFIED" = CURRENT_TIMESTAMP
                 WHERE "LEADID" = ?',
                [
                    $productIdValue,
                    $validated['COMPANYNAME'],
                    $validated['CONTACTNAME'],
                    $validated['CONTACTNO'],
                    $validated['EMAIL'],
                    $validated['ADDRESS1'] ?? null,
                    $validated['ADDRESS2'] ?? null,
                    $validated['CITY'],
                    $validated['POSTCODE'],
                    $validated['BUSINESSNATURE'],
                    $validated['USERCOUNT'] ?? null,
                    $validated['EXISTINGSOFTWARE'],
                    $validated['DEMOMODE'],
                    $descriptionValue,
                    $validated['REFERRALCODE'] ?? null,
                    $leadId,
                ]
            );
        } catch (\Throwable $e) {
            return back()->withInput($request->only(array_keys($validated)))->with('error', 'Could not update the inquiry. Please try again.');
        }

        return redirect()->route('admin.inquiries')->with('success', 'Inquiry updated.');
    }

    public function deleteInquiry(Request $request, int $leadId): \Illuminate\Http\JsonResponse
    {
        $row = DB::selectOne(
            'SELECT "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL",
                "ADDRESS1","ADDRESS2","CITY","POSTCODE","BUSINESSNATURE","USERCOUNT",
                "EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                "CURRENTSTATUS","CREATEDAT","CREATEDBY","ASSIGNED_TO","LASTMODIFIED"
             FROM "LEAD" WHERE "LEADID" = ?',
            [$leadId]
        );
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
        }

        $staleMessage = $this->incomingInquiryStaleMessage($leadId);
        if ($staleMessage !== null) {
            $code = $staleMessage === 'Lead not found.' ? 404 : 409;
            return response()->json(['success' => false, 'message' => $staleMessage], $code);
        }

        $activityRows = [];
        try {
            $activityRows = DB::select(
                'SELECT "LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS"
                 FROM "LEAD_ACT"
                 WHERE "LEADID" = ?
                 ORDER BY "CREATIONDATE" ASC, "LEAD_ACTID" ASC',
                [$leadId]
            );
        } catch (\Throwable $e) {
            $activityRows = [];
        }

        try {
            DB::delete('DELETE FROM "LEAD_ACT" WHERE "LEADID" = ?', [$leadId]);
            DB::delete('DELETE FROM "LEAD" WHERE "LEADID" = ?', [$leadId]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Could not delete inquiry.'], 500);
        }

        // Store for undo (re-insert same lead if user clicks Undo)
        $request->session()->put('delete_undo', [
            'lead_id' => $leadId,
            'lead' => (array) $row,
            'activities' => array_map(static fn ($activity) => (array) $activity, $activityRows),
        ]);

        return response()->json(['success' => true]);
    }

    public function undoDeleteInquiry(Request $request): RedirectResponse
    {
        $validated = $request->validate(['LEADID' => 'required|integer|min:1']);
        $leadId = (int) $validated['LEADID'];

        $data = $request->session()->get('delete_undo');
        if (!$data || (int) ($data['lead_id'] ?? 0) !== $leadId) {
            return redirect()->route('admin.inquiries')->with('error', 'Cannot undo: delete session expired or invalid.');
        }

        $lead = $data['lead'];
        $activities = $data['activities'] ?? [];
        try {
            DB::beginTransaction();

            DB::insert(
                'INSERT INTO "LEAD" (
                    "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL",
                    "ADDRESS1","ADDRESS2","CITY","POSTCODE","BUSINESSNATURE","USERCOUNT",
                    "EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                    "CURRENTSTATUS","CREATEDAT","CREATEDBY","ASSIGNED_TO","LASTMODIFIED"
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [
                    $leadId,
                    $lead['PRODUCTID'] ?? null,
                    $lead['COMPANYNAME'] ?? null,
                    $lead['CONTACTNAME'] ?? null,
                    $lead['CONTACTNO'] ?? null,
                    $lead['EMAIL'] ?? null,
                    $lead['ADDRESS1'] ?? null,
                    $lead['ADDRESS2'] ?? null,
                    $lead['CITY'] ?? null,
                    $lead['POSTCODE'] ?? null,
                    $lead['BUSINESSNATURE'] ?? null,
                    $lead['USERCOUNT'] ?? null,
                    $lead['EXISTINGSOFTWARE'] ?? null,
                    $lead['DEMOMODE'] ?? null,
                    $lead['DESCRIPTION'] ?? null,
                    $lead['REFERRALCODE'] ?? null,
                    $lead['CURRENTSTATUS'] ?? 'Open',
                    $lead['CREATEDAT'] ?? null,
                    $lead['CREATEDBY'] ?? null,
                    $lead['ASSIGNED_TO'] ?? null,
                    $lead['LASTMODIFIED'] ?? null,
                ]
            );

            DB::delete('DELETE FROM "LEAD_ACT" WHERE "LEADID" = ?', [$leadId]);

            foreach ($activities as $activity) {
                DB::insert(
                    'INSERT INTO "LEAD_ACT" (
                        "LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS"
                    ) VALUES (?,?,?,?,?,?,?,?)',
                    [
                        $activity['LEAD_ACTID'] ?? null,
                        $activity['LEADID'] ?? $leadId,
                        $activity['USERID'] ?? null,
                        $activity['CREATIONDATE'] ?? null,
                        $activity['SUBJECT'] ?? null,
                        $activity['DESCRIPTION'] ?? null,
                        $activity['ATTACHMENT'] ?? null,
                        $activity['STATUS'] ?? null,
                    ]
                );
            }

            DB::update(
                'UPDATE "LEAD"
                 SET "CURRENTSTATUS" = ?, "CREATEDAT" = ?, "CREATEDBY" = ?, "ASSIGNED_TO" = ?, "LASTMODIFIED" = ?
                 WHERE "LEADID" = ?',
                [
                    $lead['CURRENTSTATUS'] ?? 'Open',
                    $lead['CREATEDAT'] ?? null,
                    $lead['CREATEDBY'] ?? null,
                    $lead['ASSIGNED_TO'] ?? null,
                    $lead['LASTMODIFIED'] ?? null,
                    $leadId,
                ]
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('admin.inquiries')->with('error', 'Could not undo the delete. Please try again.');
        }

        $request->session()->forget('delete_undo');

        return redirect()->route('admin.inquiries')->with('success', 'Delete undone. Lead #SQL-' . $leadId . ' restored.');
    }

    public function dealers(): View
    {
        $items = $this->buildDealerItems();

        return view('admin.dealers', ['items' => $items, 'currentPage' => 'dealers']);
    }

    public function dealersSync(): JsonResponse
    {
        $items = $this->buildDealerItems();

        return response()->json([
            'rows_html' => view('admin.partials.dealers_rows', ['items' => $items])->render(),
            'count' => count($items),
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Serve a reward attachment by storage path (admin).
     */
    public function serveRewardAttachment(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $path = $request->query('path');
        if (!is_string($path) || $path === '') {
            return response('', 404);
        }
        $path = trim(str_replace('\\', '/', rawurldecode($path)));
        if (str_contains($path, '..') || ! str_starts_with($path, 'inquiry-attachments/')) {
            return response('', 404);
        }
        $fullPath = $this->resolveInquiryAttachmentPath($path);
        if ($fullPath === null) {
            return response('', 404);
        }
        $mime = mime_content_type($fullPath) ?: 'image/jpeg';
        return response()->file($fullPath, ['Content-Type' => $mime]);
    }

    /**
     * Serve a single activity attachment (image) for reward rows (admin).
     * Supports path-based storage or binary BLOB in DB.
     */
    public function rewardActivityAttachment(Request $request, int $leadId, int $leadActId): \Symfony\Component\HttpFoundation\Response
    {
        $row = DB::selectOne('SELECT "ATTACHMENT" FROM "LEAD_ACT" WHERE "LEAD_ACTID" = ? AND "LEADID" = ?', [$leadActId, $leadId]);
        if (!$row) {
            return response('', 404);
        }
        $attachment = $row->ATTACHMENT ?? $row->attachment ?? null;
        if ($attachment === null || trim((string) $attachment) === '') {
            return response('', 404);
        }
        $str = trim(str_replace('\\', '/', (string) $attachment));
        if (str_starts_with($str, 'inquiry-attachments') && ! preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $str)) {
            $path = str_contains($str, ',') ? trim(str_replace('\\', '/', explode(',', $str)[0])) : $str;
            $fullPath = $this->resolveInquiryAttachmentPath($path);
            if ($fullPath === null) {
                return response('', 404);
            }
            $mime = mime_content_type($fullPath) ?: 'image/jpeg';
            return response()->file($fullPath, ['Content-Type' => $mime]);
        }
        if (is_string($attachment) && strlen($attachment) > 0) {
            $mime = 'image/jpeg';
            if (preg_match('/^\x89PNG/', $attachment)) {
                $mime = 'image/png';
            } elseif (str_starts_with($attachment, "\xFF\xD8")) {
                $mime = 'image/jpeg';
            } elseif (str_starts_with($attachment, 'GIF8')) {
                $mime = 'image/gif';
            } elseif (str_starts_with($attachment, 'RIFF') && substr($attachment, 8, 4) === 'WEBP') {
                $mime = 'image/webp';
            }
            return response($attachment, 200, ['Content-Type' => $mime]);
        }
        return response('', 404);
    }

    /**
     * Send email to the dealer (assigned user) for a completed payout (uses SMTP).
     * Dealer email is taken from USERS table by ASSIGNED_TO.
     */
    public function sendPayoutEmail(Request $request): JsonResponse
    {
        $request->validate(['lead_id' => 'required|integer|min:1']);

        $leadId = (int) $request->input('lead_id');
        $lead = DB::selectOne(
            'SELECT "LEADID","COMPANYNAME","CONTACTNAME","ASSIGNED_TO","REFERRALCODE" FROM "LEAD" WHERE "LEADID" = ?',
            [$leadId]
        );
        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
        }

        $latestAct = DB::selectOne(
            'SELECT FIRST 1 "STATUS"
             FROM "LEAD_ACT"
             WHERE "LEADID" = ?
             ORDER BY "CREATIONDATE" DESC, "LEAD_ACTID" DESC',
            [$leadId]
        );
        $latestStatus = strtoupper(trim((string) ($latestAct->STATUS ?? '')));
        if ($latestStatus !== 'COMPLETED') {
            $displayStatus = $latestStatus !== '' ? ucfirst(strtolower($latestStatus)) : 'Unknown';
            return response()->json([
                'success' => false,
                'message' => 'This inquiry is already ' . $displayStatus . '. Please sync and try again.',
            ], 409);
        }

        $referralCode = trim((string) ($lead->REFERRALCODE ?? ''));
        if ($referralCode === '') {
            return response()->json(['success' => false, 'message' => 'Referral code is required before sending this email.'], 400);
        }

        $assignedTo = trim((string) ($lead->ASSIGNED_TO ?? ''));
        if ($assignedTo === '') {
            return response()->json(['success' => false, 'message' => 'No dealer assigned to this lead.'], 400);
        }

        $user = DB::selectOne(
            'SELECT "USERID","EMAIL","ALIAS","COMPANY" FROM "USERS" WHERE CAST("USERID" AS VARCHAR(50)) = ?',
            [$assignedTo]
        );
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Assigned dealer not found in users.'], 400);
        }

        $email = trim((string) ($user->EMAIL ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'No valid email address for the assigned dealer.'], 400);
        }

        $dealerName = trim((string) ($user->ALIAS ?? '')) ?: trim((string) ($user->COMPANY ?? '')) ?: 'Dealer';

        $senderAlias = '';
        $currentUserId = trim((string) ($request->session()->get('user_id') ?? ''));
        if ($currentUserId !== '') {
            $senderRow = DB::selectOne('SELECT "ALIAS" FROM "USERS" WHERE CAST("USERID" AS VARCHAR(50)) = ?', [$currentUserId]);
            $senderAlias = $senderRow ? trim((string) ($senderRow->ALIAS ?? '')) : '';
        }

        try {
            Mail::to($email)->send(new PayoutCompletedNotification(
                toEmail: $email,
                dealerName: $dealerName,
                leadId: $leadId,
                inquiryId: 'SQL-' . (string) ($lead->LEADID ?? $leadId),
                referralCode: $referralCode,
                senderAlias: $senderAlias !== '' ? $senderAlias : 'SQL LMS',
                companyName: trim((string) ($lead->COMPANYNAME ?? ''))
            ));
            return response()->json(['success' => true, 'message' => 'Email sent to dealer: ' . $email . '.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send email.'], 500);
        }
    }

    private function adminReportEstreamCompany(): string
    {
        return 'E STREAM SDN BHD';
    }

    private function adminReportDealerDisplayName(
        ?string $company,
        ?string $alias,
        ?string $email,
        ?string $fallbackId = null
    ): string {
        $company = trim((string) $company);
        $alias = trim((string) $alias);
        $email = trim((string) $email);
        $fallbackId = trim((string) $fallbackId);

        if ($company !== '' && strtoupper($company) === $this->adminReportEstreamCompany() && $alias !== '') {
            return $company . ' - ' . $alias;
        }

        if ($company !== '') {
            return $company;
        }

        if ($alias !== '') {
            return $alias;
        }

        if ($email !== '') {
            return $email;
        }

        return $fallbackId !== '' ? $fallbackId : '-';
    }

    private function adminReportDealerDisplayNames(array $userIds): array
    {
        $normalizedIds = array_values(array_unique(array_filter(array_map(
            static fn ($id) => trim((string) $id),
            $userIds
        ), static fn ($id) => $id !== '')));

        if ($normalizedIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($normalizedIds), '?'));
        $rows = DB::select(
            'SELECT "USERID", "COMPANY", "ALIAS", "EMAIL"
             FROM "USERS"
             WHERE CAST("USERID" AS VARCHAR(50)) IN (' . $placeholders . ')',
            $normalizedIds
        );

        $names = [];
        foreach ($rows as $row) {
            $dealerId = trim((string) ($row->USERID ?? ''));
            if ($dealerId === '') {
                continue;
            }

            $names[$dealerId] = $this->adminReportDealerDisplayName(
                (string) ($row->COMPANY ?? ''),
                (string) ($row->ALIAS ?? ''),
                (string) ($row->EMAIL ?? ''),
                $dealerId
            );
        }

        return $names;
    }

    private function adminReportScopeOptions(): array
    {
        return QueryCache::remember('admin_report_scope_options', function () {
            $options = [
                'all' => [
                    'label' => 'All',
                    'search' => 'all',
                ],
                'all_dealers' => [
                    'label' => 'All Dealers (No E Stream)',
                    'search' => 'all dealers no estream no e stream',
                ],
                'estream' => [
                    'label' => 'All E Stream',
                    'search' => 'all estream all e stream',
                ],
            ];

            try {
                $dealerRows = DB::select(
                    'SELECT DISTINCT u."USERID", u."COMPANY", u."ALIAS", u."EMAIL"
                     FROM "USERS" u
                     WHERE EXISTS (
                         SELECT 1
                         FROM "LEAD" l
                         WHERE TRIM(CAST(l."ASSIGNED_TO" AS VARCHAR(50))) = TRIM(CAST(u."USERID" AS VARCHAR(50)))
                     )
                     ORDER BY UPPER(TRIM(COALESCE("COMPANY", \'\'))),
                              UPPER(TRIM(COALESCE("ALIAS", \'\'))),
                              UPPER(TRIM(COALESCE("EMAIL", \'\'))),
                              "USERID"'
                );
            } catch (\Throwable $e) {
                return $options;
            }

            foreach ($dealerRows as $dealerRow) {
                $dealerId = trim((string) ($dealerRow->USERID ?? ''));
                if ($dealerId === '') {
                    continue;
                }

                $company = trim((string) ($dealerRow->COMPANY ?? ''));
                $alias = trim((string) ($dealerRow->ALIAS ?? ''));
                $email = trim((string) ($dealerRow->EMAIL ?? ''));

                if ($company !== '' && $alias !== '') {
                    $label = $company . ' - ' . $alias;
                } elseif ($company !== '') {
                    $label = $company;
                } elseif ($alias !== '') {
                    $label = $alias;
                } elseif ($email !== '') {
                    $label = $email;
                } else {
                    $label = $dealerId;
                }

                $searchTerms = array_filter([
                    $label,
                    $company,
                    $alias,
                    $email,
                    $dealerId,
                ], static fn ($value) => trim((string) $value) !== '');

                $options['dealer:' . $dealerId] = [
                    'label' => $label,
                    'company' => $company,
                    'alias' => $alias,
                    'email' => $email,
                    'search' => implode(' ', $searchTerms),
                ];
            }

            return $options;
        });
    }

    private function resolveAdminReportScope(Request $request): string
    {
        $selectedScope = trim((string) $request->query('report_scope', ''));
        if ($selectedScope === '') {
            $selectedScope = 'all';
        }

        $options = $this->adminReportScopeOptions();

        return array_key_exists($selectedScope, $options) ? $selectedScope : 'all';
    }

    private function buildAdminReportScopeSql(
        string $selectedScope,
        string $ownerColumnSql,
        string $companyColumnSql,
        bool $includeUnassignedForDealers = false
    ): array {
        $estreamCompany = $this->adminReportEstreamCompany();

        if ($selectedScope === 'all') {
            return ['', []];
        }

        if ($selectedScope === 'all_dealers') {
            if ($includeUnassignedForDealers) {
                return [
                    ' AND (' . $ownerColumnSql . ' IS NULL OR UPPER(TRIM(COALESCE(' . $companyColumnSql . ', \'\'))) <> ?)',
                    [$estreamCompany],
                ];
            }

            return [
                ' AND UPPER(TRIM(COALESCE(' . $companyColumnSql . ', \'\'))) <> ?',
                [$estreamCompany],
            ];
        }

        if ($selectedScope === 'estream') {
            return [
                ' AND UPPER(TRIM(COALESCE(' . $companyColumnSql . ', \'\'))) = ?',
                [$estreamCompany],
            ];
        }

        if (str_starts_with($selectedScope, 'dealer:')) {
            $dealerId = trim(substr($selectedScope, 7));

            return $dealerId === ''
                ? [' AND 1 = 0', []]
                : [' AND TRIM(CAST(' . $ownerColumnSql . ' AS VARCHAR(50))) = ?', [$dealerId]];
        }

        return [' AND 1 = 0', []];
    }

    private function buildAdminReportExistsScopeSql(string $selectedScope, string $ownerColumnSql): array
    {
        $estreamCompany = $this->adminReportEstreamCompany();

        if ($selectedScope === 'all') {
            return ['', []];
        }

        if ($selectedScope === 'all_dealers') {
            return [
                ' AND EXISTS (
                    SELECT 1
                    FROM "USERS" ux
                    WHERE ux."USERID" = ' . $ownerColumnSql . '
                      AND UPPER(TRIM(COALESCE(ux."COMPANY", \'\'))) <> ?
                )',
                [$estreamCompany],
            ];
        }

        if ($selectedScope === 'estream') {
            return [
                ' AND EXISTS (
                    SELECT 1
                    FROM "USERS" ux
                    WHERE ux."USERID" = ' . $ownerColumnSql . '
                      AND UPPER(TRIM(COALESCE(ux."COMPANY", \'\'))) = ?
                )',
                [$estreamCompany],
            ];
        }

        if (str_starts_with($selectedScope, 'dealer:')) {
            $dealerId = trim(substr($selectedScope, 7));

            return $dealerId === ''
                ? [' AND 1 = 0', []]
                : [' AND TRIM(CAST(' . $ownerColumnSql . ' AS VARCHAR(50))) = ?', [$dealerId]];
        }

        return [' AND 1 = 0', []];
    }

    public function reports(Request $request): View
    {
        $now = now();
        $year = (int) $request->query('year', (int) $now->format('Y'));
        $month = (int) $request->query('month', (int) $now->format('n'));
        $selectedReportScope = $this->resolveAdminReportScope($request);
        $reportScopeOptions = $this->adminReportScopeOptions();
        if ($year < 2000 || $year > 2100) {
            $year = (int) $now->format('Y');
        }
        if ($month < 1 || $month > 12) {
            $month = (int) $now->format('n');
        }
        $selectedDate = Carbon::create($year, $month, 1, 0, 0, 0);
        $prevDate = (clone $selectedDate)->subMonth();
        $selectedMonth = (int) $selectedDate->format('n');
        $selectedYear = (int) $selectedDate->format('Y');
        $selectedMonthName = $selectedDate->format('F');
        $selectedDaysInMonth = (int) $selectedDate->daysInMonth;
        $prevMonth = (int) $prevDate->format('n');
        $prevYear = (int) $prevDate->format('Y');

        [$leadScopeSql, $leadScopeBindings] = $this->buildAdminReportScopeSql(
            $selectedReportScope,
            'l."ASSIGNED_TO"',
            'u."COMPANY"',
            true
        );
        [$payoutScopeSql, $payoutScopeBindings] = $this->buildAdminReportScopeSql(
            $selectedReportScope,
            'p."USERID"',
            'u."COMPANY"'
        );

        $get = function ($row, string $name) {
            if (is_array($row)) {
                foreach ([$name, strtoupper($name), strtolower($name)] as $key) {
                    if (array_key_exists($key, $row)) {
                        return $row[$key];
                    }
                }
                return null;
            }
            foreach ([$name, strtoupper($name), strtolower($name)] as $prop) {
                if (is_object($row) && property_exists($row, $prop)) {
                    return $row->{$prop};
                }
            }
            return null;
        };

        // Lead status summary
        $leadStatusRows = DB::select(
            'SELECT l."CURRENTSTATUS" AS status, COUNT(*) AS c
             FROM "LEAD" l
             LEFT JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
             WHERE EXTRACT(YEAR FROM l."CREATEDAT") = ? AND EXTRACT(MONTH FROM l."CREATEDAT") = ?
             ' . $leadScopeSql . '
             GROUP BY l."CURRENTSTATUS"',
            array_merge([$selectedYear, $selectedMonth], $leadScopeBindings)
        );
        $leadStatus = [
            'Open' => 0,
            'Ongoing' => 0,
            'Closed' => 0,
            'Failed' => 0,
        ];
        foreach ($leadStatusRows as $row) {
            $key = (string) $get($row, 'status');
            if (isset($leadStatus[$key])) {
                $leadStatus[$key] = (int) $get($row, 'c');
            }
        }

        // Last month lead status summary for month-over-month status comparisons.
        $lastMonthLeadStatusRows = DB::select(
            'SELECT l."CURRENTSTATUS" AS status, COUNT(*) AS c
             FROM "LEAD" l
             LEFT JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
             WHERE EXTRACT(YEAR FROM l."CREATEDAT") = ? AND EXTRACT(MONTH FROM l."CREATEDAT") = ?
             ' . $leadScopeSql . '
             GROUP BY l."CURRENTSTATUS"',
            array_merge([$prevYear, $prevMonth], $leadScopeBindings)
        );
        $lastMonthLeadStatus = [
            'Open' => 0,
            'Ongoing' => 0,
            'Closed' => 0,
            'Failed' => 0,
        ];
        foreach ($lastMonthLeadStatusRows as $row) {
            $key = (string) $get($row, 'status');
            if (isset($lastMonthLeadStatus[$key])) {
                $lastMonthLeadStatus[$key] = (int) $get($row, 'c');
            }
        }

        // Unassigned leads: match LEAD status "Open"
        $unassignedCount = $leadStatus['Open'] ?? 0;
        $lastMonthUnassignedCount = $lastMonthLeadStatus['Open'] ?? 0;

        $normalizeActivityStatus = function ($status) {
            $raw = trim((string) $status);
            if ($raw === '') {
                return null;
            }

            return match (strtoupper($raw)) {
                'CREATED', 'OPEN' => 'Created',
                'PENDING' => 'Pending',
                'FOLLOW UP', 'FOLLOWUP' => 'FollowUp',
                'DEMO' => 'Demo',
                'CONFIRMED', 'CASE CONFIRMED' => 'Confirmed',
                'COMPLETED', 'CASE COMPLETED', 'CLOSED' => 'Completed',
                'FAILED' => 'Failed',
                'REWARDED', 'REWARD', 'REWARD DISTRIBUTED', 'PAID' => 'reward',
                default => null,
            };
        };

        // Activity status: use LATEST LEAD_ACT per LEADID (by CREATIONDATE)
        $pendingActs = DB::select(
            'SELECT a."STATUS" AS status, COUNT(*) AS c
             FROM "LEAD_ACT" a
             JOIN (
                 SELECT "LEADID", MAX("CREATIONDATE") AS max_created
                 FROM "LEAD_ACT"
                 WHERE EXTRACT(YEAR FROM "CREATIONDATE") = ? AND EXTRACT(MONTH FROM "CREATIONDATE") = ?
                 GROUP BY "LEADID"
             ) m ON m."LEADID" = a."LEADID" AND m.max_created = a."CREATIONDATE"
             JOIN "LEAD" l ON l."LEADID" = a."LEADID"
             LEFT JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
             WHERE 1 = 1
             ' . $leadScopeSql . '
             GROUP BY a."STATUS"',
            array_merge([$selectedYear, $selectedMonth], $leadScopeBindings)
        );
        $activityStatus = [
            'Created' => 0,
            'Pending' => 0,
            'FollowUp' => 0,
            'Demo' => 0,
            'Confirmed' => 0,
            'Completed' => 0,
            'Failed' => 0,
            'reward' => 0,
        ];
        foreach ($pendingActs as $row) {
            $key = $normalizeActivityStatus($get($row, 'status'));
            if ($key !== null && isset($activityStatus[$key])) {
                $activityStatus[$key] += (int) $get($row, 'c');
            }
        }

        // Last month activity by status (latest LEAD_ACT per LEADID in that month)
        $lastMonthActs = DB::select(
            'SELECT a."STATUS" AS status, COUNT(*) AS c
             FROM "LEAD_ACT" a
             JOIN (
                 SELECT "LEADID", MAX("CREATIONDATE") AS max_created
                 FROM "LEAD_ACT"
                 WHERE EXTRACT(YEAR FROM "CREATIONDATE") = ? AND EXTRACT(MONTH FROM "CREATIONDATE") = ?
                 GROUP BY "LEADID"
             ) m ON m."LEADID" = a."LEADID" AND m.max_created = a."CREATIONDATE"
             JOIN "LEAD" l ON l."LEADID" = a."LEADID"
             LEFT JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
             WHERE 1 = 1
             ' . $leadScopeSql . '
             GROUP BY a."STATUS"',
            array_merge([$prevYear, $prevMonth], $leadScopeBindings)
        );
        $lastMonthActivity = [
            'Created' => 0,
            'Pending' => 0,
            'FollowUp' => 0,
            'Demo' => 0,
            'Confirmed' => 0,
            'Completed' => 0,
            'Failed' => 0,
            'reward' => 0,
        ];
        foreach ($lastMonthActs as $row) {
            $key = $normalizeActivityStatus($get($row, 'status'));
            if ($key !== null && isset($lastMonthActivity[$key])) {
                $lastMonthActivity[$key] += (int) $get($row, 'c');
            }
        }

        // Payout summary
        $payoutRows = DB::select(
            'SELECT p."STATUS" AS status, COUNT(*) AS c
             FROM "REFERRER_PAYOUT" p
             LEFT JOIN "USERS" u ON u."USERID" = p."USERID"
             WHERE EXTRACT(YEAR FROM p."DATEGENERATED") = ? AND EXTRACT(MONTH FROM p."DATEGENERATED") = ?
             ' . $payoutScopeSql . '
             GROUP BY p."STATUS"',
            array_merge([$selectedYear, $selectedMonth], $payoutScopeBindings)
        );
        $payoutStatus = [
            'Awaiting Deal Completion' => 0,
            'Pending' => 0,
            'Paid' => 0,
        ];
        foreach ($payoutRows as $row) {
            $key = (string) $get($row, 'status');
            if (isset($payoutStatus[$key])) {
                $payoutStatus[$key] = (int) $get($row, 'c');
            }
        }

        // Last month payout by status
        $lastMonthPayoutRows = DB::select(
            'SELECT p."STATUS" AS status, COUNT(*) AS c
             FROM "REFERRER_PAYOUT" p
             LEFT JOIN "USERS" u ON u."USERID" = p."USERID"
             WHERE EXTRACT(YEAR FROM p."DATEGENERATED") = ? AND EXTRACT(MONTH FROM p."DATEGENERATED") = ?
             ' . $payoutScopeSql . '
             GROUP BY p."STATUS"',
            array_merge([$prevYear, $prevMonth], $payoutScopeBindings)
        );
        $lastMonthPayout = [
            'Awaiting Deal Completion' => 0,
            'Pending' => 0,
            'Paid' => 0,
        ];
        foreach ($lastMonthPayoutRows as $row) {
            $key = (string) $get($row, 'status');
            if (isset($lastMonthPayout[$key])) {
                $lastMonthPayout[$key] = (int) $get($row, 'c');
            }
        }

        // Inquiry trend for current month (leads created)
        $trendRows = DB::select(
            'SELECT EXTRACT(DAY FROM l."CREATEDAT") AS d, COUNT(*) AS c
             FROM "LEAD" l
             LEFT JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
             WHERE EXTRACT(MONTH FROM l."CREATEDAT") = ? AND EXTRACT(YEAR FROM l."CREATEDAT") = ?
             ' . $leadScopeSql . '
             GROUP BY EXTRACT(DAY FROM l."CREATEDAT")
             ORDER BY d',
            array_merge([$selectedMonth, $selectedYear], $leadScopeBindings)
        );
        $inquiryTrend = [];
        $trendByDay = [];
        foreach ($trendRows as $row) {
            $day = (int) $get($row, 'd');
            $count = (int) $get($row, 'c');
            $inquiryTrend[] = ['day' => $day, 'count' => $count];
            $trendByDay[$day] = $count;
        }

        $currentMonthTotal = array_sum($trendByDay);
        $lastMonthRows = DB::select(
            'SELECT COUNT(*) AS c
             FROM "LEAD" l
             LEFT JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
             WHERE EXTRACT(YEAR FROM l."CREATEDAT") = ? AND EXTRACT(MONTH FROM l."CREATEDAT") = ?
             ' . $leadScopeSql,
            array_merge([$prevYear, $prevMonth], $leadScopeBindings)
        );
        $lastMonthTotal = (int) ($lastMonthRows[0]->c ?? 0);
        $inquiryTrendPercentChange = $lastMonthTotal > 0
            ? round(($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal * 100)
            : ($currentMonthTotal > 0 ? 100 : 0);

        // Percent change vs last month for each metric
        $percentChange = function ($current, $lastMonth) {
            if ($lastMonth == 0) {
                return $current > 0 ? 100 : 0;
            }
            return (int) round(($current - $lastMonth) / $lastMonth * 100);
        };
        $metricPercent = [
            'unassigned' => $percentChange($unassignedCount, $lastMonthUnassignedCount),
            'Pending' => $percentChange($activityStatus['Pending'] ?? 0, $lastMonthActivity['Pending'] ?? 0),
            'FollowUp' => $percentChange($activityStatus['FollowUp'] ?? 0, $lastMonthActivity['FollowUp'] ?? 0),
            'Demo' => $percentChange($activityStatus['Demo'] ?? 0, $lastMonthActivity['Demo'] ?? 0),
            'Confirmed' => $percentChange($activityStatus['Confirmed'] ?? 0, $lastMonthActivity['Confirmed'] ?? 0),
            'Completed' => $percentChange($activityStatus['Completed'] ?? 0, $lastMonthActivity['Completed'] ?? 0),
            'Failed' => $percentChange($activityStatus['Failed'] ?? 0, $lastMonthActivity['Failed'] ?? 0),
            'Rewarded' => $percentChange($activityStatus['reward'] ?? 0, $lastMonthActivity['reward'] ?? 0),
        ];

        // Product Conversion Rate (from LEAD_ACT.DEALTPRODUCT) for current month
        $productIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        $productNames = [
            1 => 'SQL Account',
            2 => 'SQL Payroll',
            3 => 'SQL Production',
            4 => 'Mobile Sales',
            5 => 'SQL Ecommerce',
            6 => 'SQL EBI Wellness POS',
            7 => 'SQL X Suduai',
            8 => 'SQL X-Store',
            9 => 'SQL Vision',
            10 => 'SQL HRMS',
            11 => 'Others',
        ];
        $productCounts = array_fill_keys($productIds, 0);
        $dealRows = DB::select(
            'SELECT a."DEALTPRODUCT" AS dealt
             FROM "LEAD_ACT" a
             JOIN "LEAD" l ON l."LEADID" = a."LEADID"
             LEFT JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
             WHERE a."DEALTPRODUCT" IS NOT NULL
               AND TRIM(a."DEALTPRODUCT") <> \'\'
               AND EXTRACT(MONTH FROM a."CREATIONDATE") = ?
               AND EXTRACT(YEAR FROM a."CREATIONDATE") = ?
               ' . $leadScopeSql,
            array_merge([$selectedMonth, $selectedYear], $leadScopeBindings)
        );
        foreach ($dealRows as $row) {
            $val = trim((string) ($get($row, 'dealt') ?? ''));
            if ($val === '') {
                continue;
            }
            $ids = array_map('intval', array_filter(preg_split('/[\s,\(\)]+/', $val)));
            foreach ($ids as $pid) {
                if (isset($productCounts[$pid])) {
                    $productCounts[$pid]++;
                }
            }
        }
        $productConversion = [];
        foreach ($productIds as $pid) {
            $productConversion[] = [
                'label' => $productNames[$pid] ?? ('Product ' . $pid),
                'count' => (int) ($productCounts[$pid] ?? 0),
            ];
        }

        return view('admin.reports', [
            'currentPage' => 'reports',
            'leadStatus' => $leadStatus,
            'unassignedLeads' => (int) $unassignedCount,
            'activityStatus' => $activityStatus,
            'metricLeadStatus' => $leadStatus,
            'metricUnassignedLeads' => (int) $unassignedCount,
            'metricActivityStatus' => $activityStatus,
            'payoutStatus' => $payoutStatus,
            'metricPercent' => $metricPercent,
            'inquiryTrend' => $inquiryTrend,
            'inquiryTrendPercentChange' => $inquiryTrendPercentChange,
            'productConversion' => $productConversion,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'selectedMonthName' => $selectedMonthName,
            'selectedDaysInMonth' => $selectedDaysInMonth,
            'selectedReportScope' => $selectedReportScope,
            'reportScopeOptions' => $reportScopeOptions,
            'monthOptions' => range(1, 12),
            'yearOptions' => range(((int) $now->format('Y')) - 4, ((int) $now->format('Y'))),
        ]);
    }

    public function reportsV2(Request $request): View
    {
        $daysParam = $request->query('days', '90');
        $compareDaysParam = $request->query('compare_days', '30');
        $primaryFrom = trim((string) $request->query('primary_from', ''));
        $primaryTo = trim((string) $request->query('primary_to', ''));
        $compareFrom = trim((string) $request->query('compare_from', ''));
        $compareTo = trim((string) $request->query('compare_to', ''));
        $selectedReportScope = $this->resolveAdminReportScope($request);
        $reportScopeOptions = $this->adminReportScopeOptions();
        [$dealerScopeSql, $dealerScopeBindings] = $this->buildAdminReportExistsScopeSql(
            $selectedReportScope,
            'l."ASSIGNED_TO"'
        );

        $useCustomPrimary = $primaryFrom !== '' && $primaryTo !== '';
        $useCustomCompare = $compareFrom !== '' && $compareTo !== '';

        // Primary: N days (use Firebird DATEADD) or custom range (use CAST timestamp)
        $days = 90;
        if ($useCustomPrimary) {
            try {
                $primaryStart = Carbon::parse($primaryFrom)->startOfDay();
                $primaryEnd = Carbon::parse($primaryTo)->endOfDay();
                if ($primaryStart->gt($primaryEnd)) {
                    $useCustomPrimary = false;
                } else {
                    $primaryStartStr = $primaryStart->format('Y-m-d H:i:s');
                    $primaryEndStr = $primaryEnd->format('Y-m-d H:i:s');
                    $days = (int) round($primaryStart->diffInDays($primaryEnd)) ?: 90;
                }
            } catch (\Throwable $e) {
                $useCustomPrimary = false;
            }
        }
        if (!$useCustomPrimary) {
            $days = (int) $daysParam;
            if (!in_array($days, [30, 60, 90], true)) {
                $days = 90;
            }
        }

        $compareDays = (int) $compareDaysParam;
        if (!in_array($compareDays, [30, 60, 90], true)) {
            $compareDays = 30;
        }

        if ($useCustomCompare) {
            try {
                $compareStart = Carbon::parse($compareFrom)->startOfDay();
                $compareEnd = Carbon::parse($compareTo)->endOfDay();
                if ($compareStart->gt($compareEnd)) {
                    $useCustomCompare = false;
                } else {
                    $compareStartStr = $compareStart->format('Y-m-d H:i:s');
                    $compareEndStr = $compareEnd->format('Y-m-d H:i:s');
                }
            } catch (\Throwable $e) {
                $useCustomCompare = false;
            }
        }
        if (!$useCustomCompare) {
            $compareStartStr = null;
            $compareEndStr = null;
        }

        // Build primary period filter: either DATEADD (preset) or timestamp bounds (custom)
        if ($useCustomPrimary) {
            $dealerTotals = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id,
                        COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL") AS name,
                        COUNT(*) AS total_c,
                        SUM(CASE WHEN l."CURRENTSTATUS" = ? THEN 1 ELSE 0 END) AS closed_c
                 FROM "LEAD" l
                 JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   AND l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP)
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO", COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL")',
                array_merge(['Closed', $primaryStartStr, $primaryEndStr], $dealerScopeBindings)
            );
        } else {
            $dealerTotals = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id,
                        COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL") AS name,
                        COUNT(*) AS total_c,
                        SUM(CASE WHEN l."CURRENTSTATUS" = ? THEN 1 ELSE 0 END) AS closed_c
                 FROM "LEAD" l
                 JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   AND l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE)
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO", COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL")',
                array_merge(['Closed', -$days], $dealerScopeBindings)
            );
        }

        $dealerNameById = $this->adminReportDealerDisplayNames(array_map(
            static fn ($row) => (string) ($row->DEALER_ID ?? $row->dealer_id ?? ''),
            $dealerTotals
        ));

        $totalsByDealer = [];
        foreach ($dealerTotals as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '') continue;
            $total = (int) ($r->TOTAL_C ?? $r->total_c ?? 0);
            $closed = (int) ($r->CLOSED_C ?? $r->closed_c ?? 0);
            $totalsByDealer[$id] = [
                'dealer_id' => $id,
                'name' => $dealerNameById[$id] ?? (string) ($r->NAME ?? $r->name ?? $id),
                'total' => $total,
                'closed' => $closed,
                'closed_rate' => $total > 0 ? ($closed / $total * 100) : 0,
                'rejected' => 0,
                'rejection_rate' => 0,
            ];
        }

        // "Rejection" proxy: Closed leads without any Completed activity record (primary period)
        if ($useCustomPrimary) {
            $rejectedRows = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id, COUNT(*) AS c
                 FROM "LEAD" l
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   AND l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP)
                   AND l."CURRENTSTATUS" = ?
                   AND NOT EXISTS (SELECT 1 FROM "LEAD_ACT" a WHERE a."LEADID" = l."LEADID" AND a."STATUS" = ?)
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO"',
                array_merge([$primaryStartStr, $primaryEndStr, 'Closed', 'Completed'], $dealerScopeBindings)
            );
        } else {
            $rejectedRows = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id, COUNT(*) AS c
                 FROM "LEAD" l
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   AND l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE)
                   AND l."CURRENTSTATUS" = ?
                   AND NOT EXISTS (SELECT 1 FROM "LEAD_ACT" a WHERE a."LEADID" = l."LEADID" AND a."STATUS" = ?)
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO"',
                array_merge([-$days, 'Closed', 'Completed'], $dealerScopeBindings)
            );
        }
        foreach ($rejectedRows as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '' || !isset($totalsByDealer[$id])) continue;
            $rej = (int) ($r->C ?? $r->c ?? 0);
            $totalsByDealer[$id]['rejected'] = $rej;
            $total = (int) $totalsByDealer[$id]['total'];
            $totalsByDealer[$id]['rejection_rate'] = $total > 0 ? ($rej / $total * 100) : 0;
        }

        // Failed count (CURRENTSTATUS = Failed) in primary period
        if ($useCustomPrimary) {
            $failedCountRows = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id, COUNT(*) AS failed_c
                 FROM "LEAD" l
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   AND l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP)
                   AND TRIM(COALESCE(l."CURRENTSTATUS", \'\')) = ?
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO"',
                array_merge([$primaryStartStr, $primaryEndStr, 'Failed'], $dealerScopeBindings)
            );
        } else {
            $failedCountRows = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id, COUNT(*) AS failed_c
                FROM "LEAD" l
                WHERE l."ASSIGNED_TO" IS NOT NULL
                   AND l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE)
                   AND TRIM(COALESCE(l."CURRENTSTATUS", \'\')) = ?
                   ' . $dealerScopeSql . '
                GROUP BY l."ASSIGNED_TO"',
                array_merge([-$days, 'Failed'], $dealerScopeBindings)
            );
        }
        foreach ($failedCountRows as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '' || !isset($totalsByDealer[$id])) continue;
            $totalsByDealer[$id]['failed'] = (int) ($r->FAILED_C ?? $r->failed_c ?? 0);
            $total = (int) $totalsByDealer[$id]['total'];
            $totalsByDealer[$id]['fail_rate'] = $total > 0 ? round($totalsByDealer[$id]['failed'] / $total * 100, 1) : 0;
        }
        foreach ($totalsByDealer as $id => $d) {
            if (!isset($d['failed'])) {
                $totalsByDealer[$id]['failed'] = 0;
                $totalsByDealer[$id]['fail_rate'] = 0.0;
            }
        }

        // Comparison period: total and failed per dealer for increase fail rate
        if ($useCustomCompare) {
            $compareTotals = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id,
                        COUNT(*) AS total_c,
                        SUM(CASE WHEN TRIM(COALESCE(l."CURRENTSTATUS", \'\')) = ? THEN 1 ELSE 0 END) AS failed_c
                 FROM "LEAD" l
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   AND l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP)
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO"',
                array_merge(['Failed', $compareStartStr, $compareEndStr], $dealerScopeBindings)
            );
        } else {
            $compareTotals = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id,
                        COUNT(*) AS total_c,
                        SUM(CASE WHEN TRIM(COALESCE(l."CURRENTSTATUS", \'\')) = ? THEN 1 ELSE 0 END) AS failed_c
                 FROM "LEAD" l
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   AND l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE)
                   AND l."CREATEDAT" <= CURRENT_DATE
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO"',
                array_merge(['Failed', -$compareDays], $dealerScopeBindings)
            );
        }
        $compareByDealer = [];
        foreach ($compareTotals as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '') continue;
            $total = (int) ($r->TOTAL_C ?? $r->total_c ?? 0);
            $failed = (int) ($r->FAILED_C ?? $r->failed_c ?? 0);
            $compareByDealer[$id] = [
                'total' => $total,
                'failed' => $failed,
                'fail_rate' => $total > 0 ? round($failed / $total * 100, 1) : 0,
            ];
        }

        $highestClosed = null;
        $highestRejected = null;
        foreach ($totalsByDealer as $d) {
            if ($highestClosed === null || $d['closed_rate'] > $highestClosed['closed_rate']) {
                $highestClosed = $d;
            }
            if ($highestRejected === null || $d['rejection_rate'] > $highestRejected['rejection_rate']) {
                $highestRejected = $d;
            }
        }

        // Variance %: primary vs compare period
        if ($useCustomPrimary && $useCustomCompare) {
            $varianceRows = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id,
                        SUM(CASE WHEN l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP) THEN 1 ELSE 0 END) AS curr_c,
                        SUM(CASE WHEN l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP) THEN 1 ELSE 0 END) AS last_c
                 FROM "LEAD" l
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO"',
                array_merge([$primaryStartStr, $primaryEndStr, $compareStartStr, $compareEndStr], $dealerScopeBindings)
            );
        } else {
            $varianceRows = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id,
                        SUM(CASE WHEN l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE) AND l."CREATEDAT" <= CURRENT_DATE THEN 1 ELSE 0 END) AS curr_c,
                        SUM(CASE WHEN l."CREATEDAT" >= DATEADD(DAY, ?, DATEADD(YEAR, -1, CURRENT_DATE)) AND l."CREATEDAT" <= DATEADD(YEAR, -1, CURRENT_DATE) THEN 1 ELSE 0 END) AS last_c
                 FROM "LEAD" l
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO"',
                array_merge([-$days, -$days], $dealerScopeBindings)
            );
        }
        $variance = [];
        foreach ($varianceRows as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '' || !isset($totalsByDealer[$id])) continue;
            $curr = (int) ($r->CURR_C ?? $r->curr_c ?? 0);
            $last = (int) ($r->LAST_C ?? $r->last_c ?? 0);
            $pct = $last > 0 ? (int) round(($curr - $last) / $last * 100) : ($curr > 0 ? 100 : 0);
            $variance[] = ['dealer_id' => $id, 'name' => $totalsByDealer[$id]['name'], 'delta' => $pct];
        }
        usort($variance, function ($a, $b) { return abs($b['delta']) <=> abs($a['delta']); });
        $variance = array_slice($variance, 0, 10);

        // Last activity per dealer (any lead activity)
        $lastActivityRows = DB::select(
            'SELECT l."ASSIGNED_TO" AS dealer_id,
                    MAX(a."CREATIONDATE") AS last_at
             FROM "LEAD_ACT" a
             JOIN "LEAD" l ON l."LEADID" = a."LEADID"
             WHERE l."ASSIGNED_TO" IS NOT NULL
               ' . $dealerScopeSql . '
             GROUP BY l."ASSIGNED_TO"'
            ,
            $dealerScopeBindings
        );
        $lastActivityByDealer = [];
        foreach ($lastActivityRows as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '') continue;
            $lastAt = $r->LAST_AT ?? $r->last_at ?? null;
            if ($lastAt) {
                $dt = \Carbon\Carbon::parse($lastAt);
                $lastActivityByDealer[$id] = [
                    'date' => $dt->format('Y-m-d'),
                    'days_ago' => (int) $dt->diffInDays(now()),
                ];
            }
        }

        // Action list (at-risk): dealers with increase in fail rate (same period filter as bar chart)
        // Dealer name from USERS via ASSIGNED_TO; fail count & fail rate from LEAD (Failed) in period
        $atRiskRows = [];
        foreach ($totalsByDealer as $id => $d) {
            $currentFailRate = (float) ($d['fail_rate'] ?? 0);
            $lastFailRate = isset($compareByDealer[$id]) ? (float) $compareByDealer[$id]['fail_rate'] : 0;
            // Percentage increase in fail rate vs comparison period
            if ($lastFailRate > 0) {
                $increasePct = round(($currentFailRate - $lastFailRate) / $lastFailRate * 100, 1);
            } else {
                $increasePct = $currentFailRate > 0 ? 100.0 : 0.0;
            }
            $atRiskRows[] = [
                'id' => $id,
                'name' => $d['name'],
                'fail_count' => (int) ($d['failed'] ?? 0),
                'fail_rate' => $currentFailRate,
                'increase_fail_rate' => $increasePct,
                'last_activity_days' => $lastActivityByDealer[$id]['days_ago'] ?? null,
                'last_activity' => $lastActivityByDealer[$id]['date'] ?? '—',
            ];
        }
        usort($atRiskRows, function ($a, $b) {
            return $b['increase_fail_rate'] <=> $a['increase_fail_rate'];
        });
        // Only dealers with increase_fail_rate >= 30%
        $atRiskFiltered = array_values(array_filter($atRiskRows, fn ($r) => ($r['increase_fail_rate'] ?? 0) >= 30));
        $criticalDropsCount = count($atRiskFiltered);

        // No pagination: show full list and let page scroll naturally.
        $atRiskTotal = $criticalDropsCount;
        $atRiskPerPage = $atRiskTotal > 0 ? $atRiskTotal : 10;
        $atRiskPage = 1;
        $atRisk = $atRiskFiltered;
        $atRiskTotalPages = 1;

        // Top 10 dealers by Failed count (CurrentStatus = Failed), primary period
        if ($useCustomPrimary) {
            $failedRows = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id,
                        COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL") AS name,
                        COUNT(*) AS failed_c
                 FROM "LEAD" l
                 JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   AND l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP)
                   AND TRIM(COALESCE(l."CURRENTSTATUS", \'\')) = ?
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO", COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL")
                 ORDER BY failed_c DESC',
                array_merge([$primaryStartStr, $primaryEndStr, 'Failed'], $dealerScopeBindings)
            );
        } else {
            $failedRows = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id,
                        COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL") AS name,
                        COUNT(*) AS failed_c
                 FROM "LEAD" l
                 JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   AND l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE)
                   AND TRIM(COALESCE(l."CURRENTSTATUS", \'\')) = ?
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO", COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL")
                 ORDER BY failed_c DESC',
                array_merge([-$days, 'Failed'], $dealerScopeBindings)
            );
        }
        $top10Failed = [];
        foreach (array_slice($failedRows, 0, 5) as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            $failed = (int) ($r->FAILED_C ?? $r->failed_c ?? 0);
            $total = isset($totalsByDealer[$id]) ? (int) $totalsByDealer[$id]['total'] : $failed;
            $top10Failed[] = [
                'dealer_id' => $id,
                'name' => $dealerNameById[$id] ?? (string) ($r->NAME ?? $r->name ?? $id),
                'count' => $failed,
                'total_assigned' => $total,
                'percentage' => $total > 0 ? round($failed / $total * 100, 1) : 0,
            ];
        }

        // Top 10 dealers by Closed count (CurrentStatus = Closed), primary period
        if ($useCustomPrimary) {
            $closedRows = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id,
                        COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL") AS name,
                        COUNT(*) AS closed_c
                 FROM "LEAD" l
                 JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   AND l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP)
                   AND TRIM(COALESCE(l."CURRENTSTATUS", \'\')) = ?
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO", COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL")
                 ORDER BY closed_c DESC',
                array_merge([$primaryStartStr, $primaryEndStr, 'Closed'], $dealerScopeBindings)
            );
        } else {
            $closedRows = DB::select(
                'SELECT l."ASSIGNED_TO" AS dealer_id,
                        COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL") AS name,
                        COUNT(*) AS closed_c
                 FROM "LEAD" l
                 JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
                 WHERE l."ASSIGNED_TO" IS NOT NULL
                   AND l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE)
                   AND TRIM(COALESCE(l."CURRENTSTATUS", \'\')) = ?
                   ' . $dealerScopeSql . '
                 GROUP BY l."ASSIGNED_TO", COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL")
                 ORDER BY closed_c DESC',
                array_merge([-$days, 'Closed'], $dealerScopeBindings)
            );
        }
        $top10Closed = [];
        foreach (array_slice($closedRows, 0, 5) as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            $closed = (int) ($r->CLOSED_C ?? $r->closed_c ?? 0);
            $total = isset($totalsByDealer[$id]) ? (int) $totalsByDealer[$id]['total'] : $closed;
            $top10Closed[] = [
                'dealer_id' => $id,
                'name' => $dealerNameById[$id] ?? (string) ($r->NAME ?? $r->name ?? $id),
                'count' => $closed,
                'total_assigned' => $total,
                'percentage' => $total > 0 ? round($closed / $total * 100, 1) : 0,
            ];
        }

        return view('admin.reports_v2', [
            'currentPage' => 'reports',
            'selectedReportScope' => $selectedReportScope,
            'reportScopeOptions' => $reportScopeOptions,
            'topVariance' => $variance,
            'highestClosed' => $highestClosed,
            'highestRejected' => $highestRejected,
            'atRisk' => $atRisk,
            'atRiskTotal' => $atRiskTotal,
            'atRiskPage' => $atRiskPage,
            'atRiskPerPage' => $atRiskPerPage,
            'atRiskTotalPages' => $atRiskTotalPages,
            'criticalDropsCount' => $criticalDropsCount,
            'top10Failed' => $top10Failed,
            'top10Closed' => $top10Closed,
            'chartDays' => $days,
        ]);
    }

    /** Dealer activity (LEAD_ACT) for a dealer — view-only for Reports V2 "Log Intervention" popout */
    public function dealerActivity(string $userid): \Illuminate\Http\JsonResponse
    {
        $rows = DB::select(
            'SELECT a."LEAD_ACTID", a."LEADID", a."USERID", a."CREATIONDATE", a."SUBJECT", a."DESCRIPTION", a."STATUS"
             FROM "LEAD_ACT" a
             INNER JOIN "LEAD" l ON l."LEADID" = a."LEADID" AND l."ASSIGNED_TO" = ?
             ORDER BY a."CREATIONDATE" DESC, a."LEAD_ACTID" DESC',
            [$userid]
        );
        $items = array_map(fn ($r) => [
            'LEAD_ACTID' => $r->LEAD_ACTID,
            'LEADID' => $r->LEADID,
            'USERID' => $r->USERID,
            'CREATIONDATE' => $r->CREATIONDATE,
            'SUBJECT' => $r->SUBJECT,
            'DESCRIPTION' => $r->DESCRIPTION,
            'STATUS' => $r->STATUS,
        ], $rows);
        return response()->json(['items' => $items]);
    }

    public function reportsRevenue(Request $request): View
    {
        $quarter = strtoupper((string) $request->query('quarter', ''));
        $year = (int) $request->query('year', (int) now()->format('Y'));
        $selectedReportScope = $this->resolveAdminReportScope($request);
        $reportScopeOptions = $this->adminReportScopeOptions();

        if (!in_array($quarter, ['Q1', 'Q2', 'Q3', 'Q4'], true)) {
            $m = (int) now()->format('n');
            $q = (int) ceil($m / 3);
            $quarter = 'Q' . $q;
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) now()->format('Y');
        }

        $qNum = (int) substr($quarter, 1, 1);
        $startMonth = ($qNum - 1) * 3 + 1;
        $start = Carbon::create($year, $startMonth, 1, 0, 0, 0);
        $end = (clone $start)->addMonths(3)->subSecond(); // inclusive end

        $startStr = $start->format('Y-m-d H:i:s');
        $endStr = $end->format('Y-m-d H:i:s');
        [$leadScopeSql, $leadScopeBindings] = $this->buildAdminReportScopeSql(
            $selectedReportScope,
            'l."ASSIGNED_TO"',
            'u."COMPANY"'
        );
        [$productScopeSql, $productScopeBindings] = $this->buildAdminReportScopeSql(
            $selectedReportScope,
            'a."USERID"',
            'u."COMPANY"'
        );

        // Dealer performance: total/closed/failed from LEAD; rewarded from LEAD_ACT (STATUS = Rewarded)
        $rowsSql = 'SELECT u."USERID" AS dealer_id,
                    u."EMAIL" AS email,
                    TRIM(COALESCE(u."COMPANY", \'\')) AS company,
                    TRIM(COALESCE(u."ALIAS", \'\')) AS alias,
                    COUNT(*) AS total_leads,
                    SUM(CASE WHEN TRIM(COALESCE(l."CURRENTSTATUS", \'\')) = ? THEN 1 ELSE 0 END) AS closed_leads,
                    SUM(CASE WHEN TRIM(COALESCE(l."CURRENTSTATUS", \'\')) = ? THEN 1 ELSE 0 END) AS failed_leads,
                    (SELECT COUNT(DISTINCT a."LEADID")
                     FROM "LEAD_ACT" a
                     INNER JOIN "LEAD" l2 ON l2."LEADID" = a."LEADID" AND l2."ASSIGNED_TO" = u."USERID"
                       AND l2."CREATEDAT" >= ? AND l2."CREATEDAT" <= ?
                     WHERE UPPER(TRIM(COALESCE(a."STATUS", \'\'))) = ?) AS rewarded_leads
             FROM "LEAD" l
              JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
               WHERE l."ASSIGNED_TO" IS NOT NULL
                AND l."CREATEDAT" >= ?
                AND l."CREATEDAT" <= ?
                ' . $leadScopeSql . '
              GROUP BY u."USERID", u."EMAIL", u."COMPANY", u."ALIAS"
              ORDER BY total_leads DESC';
        $rowsBindings = ['Closed', 'Failed', $startStr, $endStr, 'REWARDED', $startStr, $endStr];
        $rowsBindings = array_merge($rowsBindings, $leadScopeBindings);
        $rows = DB::select($rowsSql, $rowsBindings);

        $dealers = [];
        $totalVolume = 0;
        $totalLeads = 0;
        $weightedRejection = 0;

        foreach ($rows as $r) {
            $dealerId = trim((string) ($r->DEALER_ID ?? $r->dealer_id ?? ''));
            $total = (int) ($r->TOTAL_LEADS ?? $r->total_leads ?? 0);
            $closed = (int) ($r->CLOSED_LEADS ?? $r->closed_leads ?? 0);
            $failed = (int) ($r->FAILED_LEADS ?? $r->failed_leads ?? 0);
            $rewarded = (int) ($r->REWARDED_LEADS ?? $r->rewarded_leads ?? 0);
            if ($total <= 0) {
                continue;
            }
            $rejectionRate = $total > 0 ? ($failed / $total) * 100 : 0;

            $email = (string) ($r->EMAIL ?? $r->email ?? '');
            $company = (string) ($r->COMPANY ?? $r->company ?? '');
            $alias = trim((string) ($r->ALIAS ?? $r->alias ?? ''));
            $dealers[] = [
                'dealer_id' => $dealerId,
                'email' => $email,
                'name' => $this->adminReportDealerDisplayName($company, $alias, $email, $dealerId),
                'total' => $total,
                'closed' => $closed,
                'rewarded' => $rewarded,
                'rejection_rate' => $rejectionRate,
                'converted_products' => 0,
            ];

            $totalVolume += $total;
            $totalLeads += $total;
            $weightedRejection += $rejectionRate * $total;
        }

        // Top dealer by product conversion in selected quarter:
        // count number of product ids in DEALTPRODUCT (e.g. "1,9" counts as 2).
        $topProductSql = 'SELECT a."USERID" AS dealer_id,
                    u."EMAIL" AS dealer_email,
                    TRIM(COALESCE(u."COMPANY", \'\')) AS dealer_company,
                    TRIM(COALESCE(u."ALIAS", \'\')) AS dealer_alias,
                    a."DEALTPRODUCT" AS dealt
             FROM "LEAD_ACT" a
             JOIN "LEAD" l ON l."LEADID" = a."LEADID"
             LEFT JOIN "USERS" u ON u."USERID" = a."USERID"
             WHERE a."USERID" IS NOT NULL
               AND UPPER(TRIM(COALESCE(a."USERID", \'\'))) = UPPER(TRIM(COALESCE(l."ASSIGNED_TO", \'\')))
               AND a."DEALTPRODUCT" IS NOT NULL
               AND TRIM(a."DEALTPRODUCT") <> \'\'
               AND UPPER(TRIM(COALESCE(a."STATUS", \'\'))) = ?
               AND a."CREATIONDATE" >= ?
               AND a."CREATIONDATE" <= ?
               ' . $productScopeSql;
        $topProductBindings = ['COMPLETED', $startStr, $endStr];
        $topProductBindings = array_merge($topProductBindings, $productScopeBindings);
        $topProductRows = DB::select($topProductSql, $topProductBindings);
        $topProductByDealer = [];
        foreach ($topProductRows as $r) {
            $id = trim((string) ($r->DEALER_ID ?? $r->dealer_id ?? ''));
            if ($id === '') {
                continue;
            }
            $company = (string) ($r->DEALER_COMPANY ?? $r->dealer_company ?? '');
            $alias = (string) ($r->DEALER_ALIAS ?? $r->dealer_alias ?? '');
            $email = (string) ($r->DEALER_EMAIL ?? $r->dealer_email ?? '');
            $name = $this->adminReportDealerDisplayName($company, $alias, $email, $id);
            $dealt = trim((string) ($r->DEALT ?? $r->dealt ?? ''));
            if ($dealt === '') {
                continue;
            }
            $productIds = array_map('intval', array_filter(preg_split('/[\s,\(\)]+/', $dealt)));
            $count = 0;
            foreach ($productIds as $pid) {
                if ($pid > 0) {
                    $count++;
                }
            }
            if ($count <= 0) {
                continue;
            }
            if (!isset($topProductByDealer[$id])) {
                $topProductByDealer[$id] = [
                    'dealer_id' => $id,
                    'name' => $name,
                    'converted_products' => 0,
                ];
            }
            $topProductByDealer[$id]['converted_products'] += $count;
        }
        foreach ($dealers as &$dealerRow) {
            $did = trim((string) ($dealerRow['dealer_id'] ?? ''));
            $dealerRow['converted_products'] = (int) (($topProductByDealer[$did]['converted_products'] ?? 0));
        }
        unset($dealerRow);

        usort($dealers, function ($a, $b) {
            $cmp = ((int) ($b['converted_products'] ?? 0)) <=> ((int) ($a['converted_products'] ?? 0));
            if ($cmp !== 0) {
                return $cmp;
            }
            $cmp = ((int) ($b['closed'] ?? 0)) <=> ((int) ($a['closed'] ?? 0));
            if ($cmp !== 0) {
                return $cmp;
            }
            return ((int) ($b['total'] ?? 0)) <=> ((int) ($a['total'] ?? 0));
        });
        $avgRejection = $totalLeads > 0 ? $weightedRejection / $totalLeads : 0.0;

        $topProductDealer = null;
        foreach ($dealers as $dealerRow) {
            if ((int) ($dealerRow['converted_products'] ?? 0) <= 0) {
                continue;
            }

            $topProductDealer = [
                'dealer_id' => $dealerRow['dealer_id'] ?? '',
                'name' => $dealerRow['name'] ?? '-',
                'converted_products' => (int) ($dealerRow['converted_products'] ?? 0),
            ];
            break;
        }

        // Chart: top 5 dealers by product conversion ranking.
        $chartDealers = array_slice($dealers, 0, 5);
        $chartLabels = array_column($chartDealers, 'name');
        $chartVolume = array_column($chartDealers, 'total');
        $chartClosed = array_column($chartDealers, 'closed');
        $chartRewarded = array_column($chartDealers, 'rewarded');

        // Rankings table: same top 5 dealers
        $rankings = $chartDealers;

        return view('admin.reports_revenue', [
            'currentPage' => 'reports',
            'selectedQuarter' => $quarter,
            'selectedYear' => $year,
            'selectedReportScope' => $selectedReportScope,
            'reportScopeOptions' => $reportScopeOptions,
            'yearOptions' => range(((int) now()->format('Y')) - 4, ((int) now()->format('Y'))),
            'totalVolume' => $totalVolume,
            'avgRejectionRate' => $avgRejection,
            'topProductDealer' => $topProductDealer,
            'chartLabels' => $chartLabels,
            'chartVolume' => $chartVolume,
            'chartClosed' => $chartClosed,
            'chartRewarded' => $chartRewarded,
            'rankings' => $rankings,
        ]);
    }

    public function history(Request $request): View
    {
        $historyDateFilter = $this->resolveHistoryDateFilter($request);

        $rows = DB::select(
            'SELECT FIRST 100
                "LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS"
            FROM "LEAD_ACT"
            WHERE "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?
            ORDER BY "LEAD_ACTID" DESC',
            [
                $historyDateFilter['rangeStart']->format('Y-m-d H:i:s'),
                $historyDateFilter['rangeEnd']->format('Y-m-d H:i:s'),
            ]
        );

        return view('admin.history', array_merge($historyDateFilter, [
            'items' => $rows,
            'currentPage' => 'history',
        ]));
    }

    private function resolveHistoryDateFilter(Request $request): array
    {
        $dateRange = strtolower(trim((string) $request->query('date_range', 'today')));
        $supportedRanges = ['today', 'yesterday', '2_days_ago', 'this_week', 'custom'];
        if (!in_array($dateRange, $supportedRanges, true)) {
            $dateRange = 'today';
        }

        $startDateInput = trim((string) $request->query('start_date', ''));
        $endDateInput = trim((string) $request->query('end_date', ''));

        $today = Carbon::today();
        $rangeStart = $today->copy()->startOfDay();
        $rangeEnd = $today->copy()->endOfDay();

        if ($dateRange === 'yesterday') {
            $rangeStart = $today->copy()->subDay()->startOfDay();
            $rangeEnd = $rangeStart->copy()->endOfDay();
        } elseif ($dateRange === '2_days_ago') {
            $rangeStart = $today->copy()->subDays(2)->startOfDay();
            $rangeEnd = $rangeStart->copy()->endOfDay();
        } elseif ($dateRange === 'this_week') {
            $rangeStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $rangeEnd = Carbon::now()->endOfDay();
        } elseif ($dateRange === 'custom') {
            $customStart = $this->parseHistoryFilterDate($startDateInput);
            $customEnd = $this->parseHistoryFilterDate($endDateInput);

            if ($customStart === null && $customEnd !== null) {
                $customStart = $customEnd->copy();
            }
            if ($customEnd === null && $customStart !== null) {
                $customEnd = $customStart->copy();
            }

            if ($customStart === null || $customEnd === null) {
                $dateRange = 'today';
            } else {
                if ($customStart->gt($customEnd)) {
                    [$customStart, $customEnd] = [$customEnd, $customStart];
                }

                $rangeStart = $customStart->copy()->startOfDay();
                $rangeEnd = $customEnd->copy()->endOfDay();
                $startDateInput = $customStart->format('Y-m-d');
                $endDateInput = $customEnd->format('Y-m-d');
            }
        }

        if ($dateRange !== 'custom') {
            $startDateInput = '';
            $endDateInput = '';
        }

        return [
            'dateRange' => $dateRange,
            'startDateInput' => $startDateInput,
            'endDateInput' => $endDateInput,
            'filterStartDate' => $rangeStart->format('Y-m-d'),
            'filterEndDate' => $rangeEnd->format('Y-m-d'),
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
        ];
    }

    private function parseHistoryFilterDate(string $value): ?Carbon
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);

            return $date && $date->format('Y-m-d') === $value ? $date : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function ensureMaintainUsersAccess(Request $request): ?RedirectResponse
    {
        if (strtolower((string) $request->session()->get('user_role')) === 'manager') {
            return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to access Maintain Users.');
        }

        return null;
    }

    private function normalizeInquirySnapshotValue(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d H:i:s');
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function inquiryEditSnapshotMessage(int $leadId, mixed $submittedSnapshot): ?string
    {
        $row = DB::selectOne(
            'SELECT COALESCE("LASTMODIFIED", "CREATEDAT") AS "SNAPSHOT_MODIFIED_AT"
             FROM "LEAD"
             WHERE "LEADID" = ?',
            [$leadId]
        );

        if (!$row) {
            return 'Lead not found.';
        }

        $currentSnapshot = $this->normalizeInquirySnapshotValue($row->SNAPSHOT_MODIFIED_AT ?? $row->snapshot_modified_at ?? null);
        $submittedSnapshot = $this->normalizeInquirySnapshotValue($submittedSnapshot);

        if ($currentSnapshot === null || $submittedSnapshot === null) {
            return 'This inquiry has newer changes. Please refresh and try again.';
        }

        if ($currentSnapshot !== $submittedSnapshot) {
            return 'This inquiry was updated by another admin. Please refresh and try again.';
        }

        return null;
    }

    private function loadMaintainUserPasskeyTarget(string $userid): ?object
    {
        return DB::selectOne(
            'SELECT "USERID","EMAIL","ALIAS","COMPANY","LASTLOGIN","ISACTIVE" FROM "USERS" WHERE "USERID" = ?',
            [$userid]
        );
    }

    public function maintainUsers(Request $request): View|RedirectResponse|JsonResponse
    {
        if ($denied = $this->ensureMaintainUsersAccess($request)) {
            return $denied;
        }

        $roleFilter = strtoupper(trim((string) $request->query('role', '')));
        $search = trim((string) $request->query('q', ''));
        $users = $this->loadMaintainUsersData($roleFilter, $search);
        $batchEligibleUsers = $this->maintainUsersBatchEligible($users);

        if ($request->boolean('partial') || $request->expectsJson()) {
            return response()->json([
                'rows_html' => view('admin.partials.maintain_users_rows', ['users' => $users])->render(),
                'batch_html' => view('admin.partials.maintain_users_batch_items', ['batchEligibleUsers' => $batchEligibleUsers])->render(),
                'batch_count' => count($batchEligibleUsers),
            ]);
        }

        return view('admin.maintain-users', [
            'currentPage' => 'maintain-users',
            'users' => $users,
            'batchEligibleUsers' => $batchEligibleUsers,
            'filterRole' => $roleFilter,
            'search' => $search,
        ]);
    }

    private function loadMaintainUsersData(string $roleFilter = '', string $search = ''): array
    {
        $roleFilter = strtoupper(trim($roleFilter));
        $search = trim($search);

        $params = [];
        $where = [];

        if (in_array($roleFilter, ['ADMIN', 'MANAGER', 'DEALER'], true)) {
            $where[] = 'UPPER(TRIM(u."SYSTEMROLE")) = ?';
            $params[] = $roleFilter;
        }
        if ($search !== '') {
            $like = '%' . $search . '%';
            $where[] = '('
                . 'UPPER(TRIM(COALESCE(u."EMAIL", \'\'))) LIKE UPPER(?)'
                . ' OR UPPER(TRIM(COALESCE(u."ALIAS", \'\'))) LIKE UPPER(?)'
                . ' OR UPPER(TRIM(COALESCE(u."COMPANY", \'\'))) LIKE UPPER(?)'
                . ')';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql = 'SELECT "USERID","EMAIL","SYSTEMROLE","ISACTIVE","ALIAS","COMPANY","POSTCODE","CITY","LASTLOGIN" FROM "USERS" u';
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY "USERID"';

        $rows = DB::select($sql, $params);
        $setupLinks = $this->setupLinkStore()->allSetupLinks();

        $users = array_map(function ($r) use ($setupLinks) {
            $userId = (string) ($r->USERID ?? '');
            $hasLoggedIn = $r->LASTLOGIN !== null;
            $setupLink = !$hasLoggedIn && isset($setupLinks[$userId]) ? $setupLinks[$userId] : [];
            $setupLinkEmailedAt = (string) ($setupLink['emailed_at'] ?? '');
            $setupLinkExpiresAt = (string) ($setupLink['expires_at'] ?? '');
            $setupLinkPending = $setupLinkExpiresAt !== '';
            $setupLinkExpired = false;
            if ($setupLinkPending) {
                try {
                    $setupLinkExpired = Carbon::parse($setupLinkExpiresAt)->isPast();
                } catch (\Throwable) {
                    $setupLinkExpired = true;
                }
            }

            return [
                'USERID' => $userId,
                'EMAIL' => (string) ($r->EMAIL ?? ''),
                'SYSTEMROLE' => (string) ($r->SYSTEMROLE ?? ''),
                'ISACTIVE' => (bool) ($r->ISACTIVE ?? true),
                'ALIAS' => (string) ($r->ALIAS ?? ''),
                'COMPANY' => (string) ($r->COMPANY ?? ''),
                'POSTCODE' => (string) ($r->POSTCODE ?? ''),
                'CITY' => (string) ($r->CITY ?? ''),
                'LASTLOGIN' => $r->LASTLOGIN ?? null,
                'HAS_LOGGED_IN' => $hasLoggedIn,
                'PASSKEY_SETUP_LINK_PENDING' => $setupLinkPending,
                'PASSKEY_SETUP_LINK_SENT' => $setupLinkEmailedAt !== '',
                'PASSKEY_SETUP_LINK_EXPIRED' => $setupLinkExpired,
                'PASSKEY_SETUP_LINK_EMAILED_AT' => $setupLinkEmailedAt !== '' ? $setupLinkEmailedAt : null,
                'PASSKEY_SETUP_LINK_EXPIRES_AT' => $setupLinkExpiresAt !== '' ? $setupLinkExpiresAt : null,
            ];
        }, $rows);

        return $users;
    }

    private function maintainUsersBatchEligible(array $users): array
    {
        return array_values(array_filter($users, static function ($u) {
            return !($u['HAS_LOGGED_IN'] ?? false)
                && (bool) ($u['ISACTIVE'] ?? false)
                && trim((string) ($u['EMAIL'] ?? '')) !== '';
        }));
    }

    public function maintainUsersStore(Request $request): RedirectResponse
    {
        if ($denied = $this->ensureMaintainUsersAccess($request)) {
            return $denied;
        }

        $validated = $request->validate([
            'EMAIL' => 'required|email|max:50',
            'SYSTEMROLE' => 'required|string|in:ADMIN,MANAGER,DEALER',
            'ALIAS' => 'nullable|string|max:50',
            'COMPANY' => 'nullable|string|max:40',
            'POSTCODE' => 'nullable|string|digits:5',
            'CITY' => 'nullable|string|max:100',
            'ISACTIVE' => 'nullable|boolean',
        ], [
            'EMAIL.email' => 'Invalid Email Address Format.',
            'POSTCODE.digits' => 'Invalid Postcode.',
        ]);

        $email = trim((string) $validated['EMAIL']);
        $roleInput = strtoupper(trim((string) $validated['SYSTEMROLE']));
        $estreamCompany = 'E Stream Sdn Bhd';
        $systemRole = match ($roleInput) {
            'ADMIN' => 'Admin',
            'MANAGER' => 'Manager',
            'DEALER' => 'Dealer',
            default => 'Dealer',
        };
        $isDealer = $roleInput === 'DEALER';
        $alias = trim((string) ($validated['ALIAS'] ?? ''));
        $company = trim((string) ($validated['COMPANY'] ?? ''));
        $postcode = trim((string) ($validated['POSTCODE'] ?? ''));
        $city = trim((string) ($validated['CITY'] ?? ''));
        $isActive = (bool) ($validated['ISACTIVE'] ?? true);

        if ($isDealer && ($alias === '' || $company === '' || $postcode === '' || $city === '')) {
            return back()
                ->withInput()
                ->with('error', 'Dealer accounts require alias, company, postcode, and city.');
        }

        if (!$isDealer) {
            $company = $estreamCompany;
            $postcode = '';
            $city = '';
        }

        $existing = DB::selectOne('SELECT "USERID" FROM "USERS" WHERE UPPER(TRIM("EMAIL")) = UPPER(TRIM(?))', [$email]);
        if ($existing) {
            return back()
                ->withInput()
                ->with('error', 'Email already exists for another user.');
        }

        DB::insert(
            'INSERT INTO "USERS" ("EMAIL","PASSWORDHASH","SYSTEMROLE","ISACTIVE","ALIAS","COMPANY","POSTCODE","CITY") VALUES (?,?,?,?,?,?,?,?)',
            [
                $email,
                Hash::make(Str::random(64)),
                $systemRole,
                $isActive ? 1 : 0,
                $alias !== '' ? $alias : null,
                $company !== '' ? $company : null,
                $postcode !== '' ? $postcode : '',
                $city !== '' ? $city : '',
            ]
        );

        $createdUser = DB::selectOne(
            'SELECT "USERID" FROM "USERS" WHERE UPPER(TRIM("EMAIL")) = UPPER(TRIM(?))',
            [$email]
        );

        $createAction = trim((string) $request->input('CREATE_ACTION', 'create'));
        if ($createAction === 'create_email' && $createdUser && trim((string) ($createdUser->USERID ?? '')) !== '') {
            try {
                $newUser = $this->loadMaintainUserPasskeyTarget((string) $createdUser->USERID);

                if (!$newUser || !$this->sendMaintainUserPasskeySetupLink($newUser)) {
                    return redirect()->route('admin.maintain-users')->with('error', 'User created, but failed to send passkey setup link email.');
                }

                return redirect()->route('admin.maintain-users')->with('success', 'User created and passkey setup link emailed.');
            } catch (\Throwable $e) {
                report($e);
                return redirect()->route('admin.maintain-users')->with('error', 'User created, but failed to send passkey setup link email.');
            }
        }

        return redirect()->route('admin.maintain-users')->with('success', 'User created successfully. Send a passkey setup link when ready.');
    }

    public function maintainUsersUpdate(Request $request, string $userid): RedirectResponse
    {
        if ($denied = $this->ensureMaintainUsersAccess($request)) {
            return $denied;
        }

        $existing = DB::selectOne('SELECT "USERID","SYSTEMROLE" FROM "USERS" WHERE "USERID" = ?', [$userid]);
        if (!$existing) {
            return redirect()->route('admin.maintain-users')->with('error', 'User not found.');
        }

        $validated = $request->validate([
            'EMAIL' => 'required|email|max:50',
            'ALIAS' => 'nullable|string|max:50',
            'COMPANY' => 'nullable|string|max:40',
            'POSTCODE' => 'nullable|string|digits:5',
            'CITY' => 'nullable|string|max:100',
            'ISACTIVE' => 'nullable|boolean',
            'SEND_PASSKEY_SETUP_LINK' => 'nullable|boolean',
        ], [
            'EMAIL.email' => 'Invalid Email Address Format.',
            'POSTCODE.digits' => 'Invalid Postcode.',
        ]);

        $email = trim((string) $validated['EMAIL']);
        $roleUpper = strtoupper(trim((string) ($existing->SYSTEMROLE ?? '')));
        $isDealer = $roleUpper === 'DEALER';
        $estreamCompany = 'E Stream Sdn Bhd';
        $alias = trim((string) ($validated['ALIAS'] ?? ''));
        $company = trim((string) ($validated['COMPANY'] ?? ''));
        $postcode = trim((string) ($validated['POSTCODE'] ?? ''));
        $city = trim((string) ($validated['CITY'] ?? ''));
        $isActive = (bool) ($validated['ISACTIVE'] ?? true);
        $sendPasskeySetupLink = (bool) ($validated['SEND_PASSKEY_SETUP_LINK'] ?? false);

        if ($isDealer && ($alias === '' || $company === '' || $postcode === '' || $city === '')) {
            return back()->withInput()->with('error', 'Dealer accounts require alias, company, postcode, and city.');
        }

        if (!$isDealer) {
            $company = $estreamCompany;
            $postcode = '';
            $city = '';
        }

        // Email unique except current user
        $emailConflict = DB::selectOne(
            'SELECT "USERID" FROM "USERS" WHERE UPPER(TRIM("EMAIL")) = UPPER(TRIM(?)) AND "USERID" <> ?',
            [$email, $userid]
        );
        if ($emailConflict) {
            return back()->withInput()->with('error', 'Email already exists for another user.');
        }

        // Use actual USERS table column names (same as Full Database) for Firebird compatibility
        $userRow = DB::selectOne('SELECT FIRST 1 * FROM "USERS" WHERE "USERID" = ?', [$userid]);
        $userCols = $userRow ? array_keys((array) $userRow) : [];
        $col = static function (string $logical) use ($userCols): ?string {
            foreach ($userCols as $c) {
                if (strcasecmp($c, $logical) === 0) {
                    return $c;
                }
            }
            return null;
        };
        $q = function (string $name): string {
            return '"' . str_replace('"', '""', $name) . '"';
        };
        $emailCol = $col('EMAIL');
        $aliasCol = $col('ALIAS');
        $companyCol = $col('COMPANY');
        $postcodeCol = $col('POSTCODE');
        $cityCol = $col('CITY');
        $activeCol = $col('ISACTIVE');
        $idCol = $col('USERID');
        if (!$emailCol || !$activeCol || !$idCol) {
            return back()->withInput()->with('error', 'User settings could not be loaded. Please contact support if this continues.');
        }

        $isActiveValue = $isActive ? 1 : 0;
        try {
            $parts = [$q($emailCol) . ' = ?'];
            $bind = [$email];
            if ($aliasCol) {
                $parts[] = $q($aliasCol) . ' = ?';
                $bind[] = $alias !== '' ? $alias : null;
            }
            if ($companyCol) {
                $parts[] = $q($companyCol) . ' = ?';
                $bind[] = $company !== '' ? $company : null;
            }
            if ($postcodeCol) {
                $parts[] = $q($postcodeCol) . ' = ?';
                $bind[] = $postcode !== '' ? $postcode : '';
            }
            if ($cityCol) {
                $parts[] = $q($cityCol) . ' = ?';
                $bind[] = $city !== '' ? $city : '';
            }
            $parts[] = $q($activeCol) . ' = ?';
            $bind[] = $isActiveValue;
            $bind[] = $userid;
            DB::update(
                'UPDATE "USERS" SET ' . implode(', ', $parts) . ' WHERE ' . $q($idCol) . ' = ?',
                $bind
            );
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, '23000') || str_contains($msg, 'Integrity constraint') || str_contains($msg, 'INTEG_') || str_contains($msg, 'CHECK')) {
                return back()->withInput()->with('error', 'Update could not be saved due to database rules. Please try again.');
            }
            throw $e;
        }

        $passkeySetupLinkSent = false;
        if ($sendPasskeySetupLink) {
            try {
                $updatedUser = $this->loadMaintainUserPasskeyTarget($userid);
                if (!$updatedUser || trim((string) ($updatedUser->EMAIL ?? '')) === '') {
                    return redirect()->route('admin.maintain-users')->with('error', 'User updated, but passkey setup link could not be sent because the account is missing email data.');
                }

                $this->sendMaintainUserPasskeySetupLink(
                    $updatedUser,
                    'A passkey setup link is ready for your SQL SMS account.'
                );
                $passkeySetupLinkSent = true;
            } catch (\Throwable $e) {
                report($e);
                return redirect()->route('admin.maintain-users')->with('error', 'User updated, but failed to send passkey setup link.');
            }
        }

        $successMessage = $passkeySetupLinkSent
            ? 'User updated and passkey setup link sent.'
            : 'User updated successfully.';

        return redirect()->route('admin.maintain-users')->with('success', $successMessage);
    }

    public function maintainUsersSendPasskeySetupLink(Request $request, string $userid): RedirectResponse
    {
        if ($denied = $this->ensureMaintainUsersAccess($request)) {
            return $denied;
        }

        $user = $this->loadMaintainUserPasskeyTarget($userid);

        if (!$user) {
            return redirect()->route('admin.maintain-users')->with('error', 'User not found.');
        }

        if ($user->LASTLOGIN !== null) {
            return redirect()->route('admin.maintain-users')->with('error', 'Passkey setup links can only be sent to users who have never logged in.');
        }

        try {
            $this->sendMaintainUserPasskeySetupLink($user);
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('admin.maintain-users')->with('error', 'Failed to send passkey setup link email.');
        }

        return redirect()->route('admin.maintain-users')->with('success', 'Passkey setup link emailed.');
    }

    public function maintainUsersSendPasskeySetupLinks(Request $request): RedirectResponse
    {
        if ($denied = $this->ensureMaintainUsersAccess($request)) {
            return $denied;
        }

        $selectedUserIds = array_values(array_unique(array_map(
            static fn ($value) => trim((string) $value),
            (array) $request->input('USERIDS', [])
        )));
        $selectedUserIds = array_values(array_filter($selectedUserIds, static fn ($value) => $value !== ''));

        if (empty($selectedUserIds)) {
            return redirect()->route('admin.maintain-users')->with('error', 'Please select at least one user.');
        }

        $placeholders = implode(',', array_fill(0, count($selectedUserIds), '?'));
        $users = DB::select(
            'SELECT "USERID","EMAIL","ALIAS","COMPANY","LASTLOGIN","ISACTIVE"
             FROM "USERS"
             WHERE "LASTLOGIN" IS NULL
               AND "USERID" IN (' . $placeholders . ')
             ORDER BY "USERID"'
            ,
            $selectedUserIds
        );

        $sent = 0;

        foreach ($users as $user) {
            try {
                if ($this->sendMaintainUserPasskeySetupLink($user)) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if ($sent === 0) {
            return redirect()->route('admin.maintain-users')->with('error', 'No eligible users found for passkey setup link email.');
        }

        return redirect()->route('admin.maintain-users')->with('success', 'Passkey setup link emailed to ' . $sent . ' user(s).');
    }

    private function sendMaintainUserPasskeySetupLink(object $user, ?string $introLine = null): bool
    {
        $userId = trim((string) ($user->USERID ?? ''));
        $email = trim((string) ($user->EMAIL ?? ''));
        $isActive = (bool) ($user->ISACTIVE ?? false);
        if ($userId === '' || $email === '' || !$isActive) {
            return false;
        }

        $token = $this->setupLinkStore()->issueSetupToken($userId, 1440);
        if ($token === '') {
            return false;
        }

        $recipientName = $this->maintainUserRecipientName($user);

        $systemName = trim((string) config('app.name', ''));
        if ($systemName === '' || strtoupper($systemName) === 'LARAVEL') {
            $systemName = 'SQL SMS';
        }

        Mail::to($email)->send(new UserPasskeySetupLink(
            toEmail: $email,
            recipientName: $recipientName,
            setupUrl: route('passkey.setup.form', ['token' => $token]),
            systemName: $systemName,
            subjectLine: 'Set up your SQL SMS passkey',
            introLine: $introLine ?? 'Your SQL SMS account is ready.',
            instructionLine: 'Click the link below to start setting up your passkey:',
            buttonLabel: 'Set up passkey',
            expiryLine: 'This link will expire in 24 hours.',
            ignoreLine: ''
        ));

        $this->setupLinkStore()->markSetupTokenEmailed($userId);

        return true;
    }

    private function maintainUserRecipientName(object $user): string
    {
        $email = trim((string) ($user->EMAIL ?? ''));
        $alias = trim((string) ($user->ALIAS ?? ''));
        $company = trim((string) ($user->COMPANY ?? ''));
        $companyUpper = strtoupper($company);

        if ($companyUpper === 'E STREAM SDN BHD') {
            return $alias !== '' ? $alias : $email;
        }

        return $company !== '' ? $company : ($alias !== '' ? $alias : $email);
    }

    /**
     * Send email to dealer when an inquiry is assigned to them (create or assign).
     *
     * @param string $dealerUserId USERS.USERID of the assigned dealer
     * @param int $leadId LEAD.LEADID
     * @param string|null $companyName Optional; if null, fetched from LEAD
     * @param string|null $contactName Optional; if null, fetched from LEAD
     */
    private function sendInquiryAssignedEmail(string $dealerUserId, int $leadId, ?string $companyName = null, ?string $contactName = null): void
    {
        try {
            $dealer = DB::selectOne(
                'SELECT "EMAIL", "ALIAS", "COMPANY" FROM "USERS" WHERE CAST("USERID" AS VARCHAR(50)) = ?',
                [$dealerUserId]
            );
            if (!$dealer || empty(trim((string) ($dealer->EMAIL ?? '')))) {
                return;
            }
            $dealerEmail = trim((string) $dealer->EMAIL);
            $dealerName = trim((string) ($dealer->ALIAS ?? ''));
            if ($dealerName === '') {
                $dealerName = trim((string) ($dealer->COMPANY ?? ''));
            }
            if ($dealerName === '') {
                $dealerName = $dealerEmail;
            }

            if ($companyName === null || $contactName === null) {
                $lead = DB::selectOne('SELECT "COMPANYNAME", "CONTACTNAME" FROM "LEAD" WHERE "LEADID" = ?', [$leadId]);
                $companyName = $lead ? trim((string) ($lead->COMPANYNAME ?? '')) : '';
                $contactName = $lead ? trim((string) ($lead->CONTACTNAME ?? '')) : '';
            }
            $companyName = $companyName !== '' ? $companyName : '—';
            $contactName = $contactName !== '' ? $contactName : '—';

            $viewInquiryUrl = url(route('dealer.inquiries', [], false) . '?lead=' . $leadId);

            Mail::to($dealerEmail)->send(new InquiryAssignedToDealer(
                dealerEmail: $dealerEmail,
                dealerName: $dealerName,
                leadId: $leadId,
                inquiryId: 'SQL-' . $leadId,
                companyName: $companyName,
                contactName: $contactName,
                viewInquiryUrl: $viewInquiryUrl
            ));
        } catch (\Throwable $e) {
            // Log but do not fail the request (assignment already succeeded)
            report($e);
        }
    }
}










