<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Concerns\ResolvesInquiryAttachments;
use App\Http\Controllers\Concerns\UsesSetupLinkStore;
use App\Mail\InquiryAssignedToDealer;
use App\Mail\InquiryAssignedToCustomer;
use App\Mail\PayoutCompletedNotification;
use App\Mail\UserPasskeySetupLink;
use App\Support\AppConstants;
use App\Support\AttachmentUrlBuilder;
use App\Support\DealerStatsAggregator;
use App\Support\LeadEnricher;
use App\Support\ProductConstants;
use App\Support\QueryCache;
use App\Support\StringHelper;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{

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
                    TRIM(CAST(l."ASSIGNEDTO" AS VARCHAR(50))) AS UID,
                    COUNT(*) AS TOTAL_LEAD,
                    SUM(CASE WHEN ls."LATEST_STATUS" IN (\'PENDING\', \'FOLLOWUP\', \'FOLLOW UP\', \'DEMO\', \'CONFIRMED\') THEN 1 ELSE 0 END) AS TOTAL_ONGOING,
                    SUM(CASE WHEN ls."LATEST_STATUS" IN (\'COMPLETED\', \'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\') THEN 1 ELSE 0 END) AS TOTAL_CLOSED,
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
            \Log::warning('Failed to aggregate dealer stats: '.$e->getMessage());
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
            if (! is_file($path)) {
                return $lookup;
            }

            try {
                $decoded = json_decode((string) file_get_contents($path), true);
            } catch (\Throwable $e) {
                \Log::warning('Failed to load postcode lookup file: '.$e->getMessage());

                return $lookup;
            }

            if (! is_array($decoded) || ! isset($decoded['state']) || ! is_array($decoded['state'])) {
                return $lookup;
            }

            foreach ($decoded['state'] as $state) {
                if (! is_array($state)) {
                    continue;
                }

                foreach (($state['city'] ?? []) as $city) {
                    if (! is_array($city)) {
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

                        $lookup[$normalizedPostcode] = [
                            'city' => $cityName,
                            'state' => $state['name'] ?? '',
                        ];
                    }
                }
            }

            return $lookup;
        });
    }

    private function inquiryFormViewData(?object $inquiry = null): array
    {
        $dealers = $this->buildDealerItems();

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

        $cacheKey = AppConstants::CACHE_KEY_LATEST_ASSIGNMENT.md5(implode(',', $leadIds));

        return QueryCache::remember($cacheKey, function () use ($leadIds) {
            $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
            $rows = DB::select(
                'SELECT "LEADID", "LEAD_ACTID", "USERID", "DESCRIPTION"
                 FROM "LEAD_ACT"
                 WHERE "LEADID" IN ('.$placeholders.')
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

        $cacheKey = AppConstants::CACHE_KEY_USER_DISPLAY_MAPS.md5(implode(',', $userIds));

        return QueryCache::remember($cacheKey, function () use ($userIds) {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $users = DB::select(
                'SELECT "USERID","SYSTEMROLE","ALIAS","COMPANY","EMAIL"
                 FROM "USERS"
                 WHERE CAST("USERID" AS VARCHAR(50)) IN ('.$placeholders.')',
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
            'SELECT "LEADID","ASSIGNEDTO" AS "assignedTo" FROM "LEAD" WHERE "LEADID" = ?',
            [$leadId]
        );

        if (! $lead) {
            return null;
        }

        $latest = DB::selectOne(
            'SELECT FIRST 1 "STATUS"
             FROM "LEAD_ACT"
             WHERE "LEADID" = ? AND UPPER(TRIM(COALESCE("STATUS", \'\'))) <> \'CREATED\'
             ORDER BY "CREATIONDATE" DESC, "LEAD_ACTID" DESC',
            [$leadId]
        );

        $assignedTo = StringHelper::normalize($lead->assignedTo ?? '');
        $latestStatus = strtoupper(StringHelper::normalize($latest->STATUS ?? ''));

        return [
            'assignedTo' => $assignedTo,
            'status' => $latestStatus !== '' ? $latestStatus : ($assignedTo !== '' ? 'PENDING' : 'CREATED'),
        ];
    }

    private function incomingInquiryStaleMessage(int $leadId, bool $allowPendingWhileAssigned = false): ?string
    {
        $state = $this->getLeadCurrentActionState($leadId);
        if ($state === null) {
            return AppConstants::ERR_INQUIRY_NOT_FOUND;
        }

        $status = strtoupper(StringHelper::normalize($state['status'] ?? ''));
        $assignedTo = StringHelper::normalize($state['assignedTo'] ?? '');
        if ($assignedTo !== '') {
            if ($allowPendingWhileAssigned && in_array($status, ['', 'OPEN', 'CREATED', 'PENDING'], true)) {
                return null;
            }

            $maps = $this->userDisplayMaps([$assignedTo]);
            $assignedToMap = $maps['assignedToMap'] ?? [];
            $assignedLabel = $assignedToMap[$assignedTo] ?? $assignedTo;

            return sprintf(AppConstants::ERR_INQUIRY_ALREADY_ASSIGNED, $assignedLabel);
        }

        if ($status !== '' && ! in_array($status, [AppConstants::STATUS_OPEN, AppConstants::STATUS_CREATED], true)) {
            return sprintf(AppConstants::ERR_INQUIRY_ALREADY_PROCESSED, $status);
        }

        return null;
    }

    private function dashboardData(): array
    {
        // Total leads: all rows in LEAD (excluding Cancelled)
        $leadCountRow = DB::selectOne(
            'SELECT COUNT(*) as cnt FROM "LEAD" l
             WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
             AND COALESCE((SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                           FROM "LEAD_ACT" la
                           WHERE la."LEADID" = l."LEADID"
                           ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                          ), \'\') <> \'CANCELLED\''
        );
        $totalLeads = (int) ($leadCountRow->cnt ?? $leadCountRow->CNT ?? current((array) $leadCountRow) ?? 0);

        // Total closed: LEAD_ACT with STATUS = 'Completed'
        $closedRow = DB::selectOne(
            'SELECT COUNT(*) as cnt FROM "LEAD_ACT" WHERE UPPER(TRIM("STATUS")) = \'COMPLETED\''
        );
        $totalClosed = (int) ($closedRow->cnt ?? $closedRow->CNT ?? current((array) $closedRow) ?? 0);

        // Active inquiries: leads whose latest LEAD_ACT status is ongoing
        $activeRow = DB::selectOne(
            'SELECT COUNT(*) as cnt FROM "LEAD" l
             WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
               AND l."ASSIGNEDTO" IS NOT NULL
               AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                    FROM "LEAD_ACT" la
                    WHERE la."LEADID" = l."LEADID"
                    ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                   ) IN (\'PENDING\', \'FOLLOWUP\', \'FOLLOW UP\', \'DEMO\', \'CONFIRMED\')'
        );
        $activeInquiries = (int) ($activeRow->cnt ?? $activeRow->CNT ?? current((array) $activeRow) ?? 0);

        // Conversion rate: closed / total leads
        $conversionRate = $totalLeads > 0 ? round(($totalClosed / $totalLeads) * 100, 1) : 0;
        // Average closing time: from first PENDING to first COMPLETED
        $avgClosingSeconds = null;
        $avgClosingTime = '-';

        try {
            $rows = DB::select(
                'SELECT
                    la."LEADID",
                    MIN(CASE WHEN UPPER(TRIM(la."STATUS")) = \'PENDING\' THEN la."CREATIONDATE" END) AS "PENDING_AT",
                    MIN(CASE WHEN UPPER(TRIM(la."STATUS")) IN (\'COMPLETED\', \'REWARDED\') THEN la."CREATIONDATE" END) AS "COMPLETED_AT"
                 FROM "LEAD_ACT" la
                 GROUP BY la."LEADID"'
            );

            $total = 0;
            $count = 0;

            foreach ($rows as $row) {
                $pendingAt = $row->PENDING_AT ?? $row->pending_at ?? null;
                $completedAt = $row->COMPLETED_AT ?? $row->completed_at ?? null;

                if (! $pendingAt || ! $completedAt) {
                    continue;
                }

                $pendingTs = strtotime((string) $pendingAt);
                $completedTs = strtotime((string) $completedAt);

                if (! $pendingTs || ! $completedTs || $completedTs < $pendingTs) {
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
            \Log::error('Dashboard closing time calculation failed: '.$e->getMessage());
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
        $percentChange = static function ($current, $previous): float {
            $change = $previous > 0
                ? round((($current - $previous) / $previous) * 100, 1)
                : ($current > 0 ? 100.0 : 0.0);

            return abs($change) < 0.05 ? 0.0 : $change;
        };

        // Pre-fetch activity transitions (past 180 days or start of the year, whichever is earlier)
        $activityMap = [];
        try {
            $queryStart = min(Carbon::now()->startOfYear(), Carbon::today()->subDays(180))->format('Y-m-d 00:00:00');
            $queryEnd = Carbon::now()->format('Y-m-d 23:59:59');

            $actRows = DB::select(
                'SELECT 
                    CAST(la."CREATIONDATE" AS DATE) AS "ACT_DATE", 
                    UPPER(TRIM(la."STATUS")) AS "ACT_STATUS", 
                    COUNT(*) as "CNT" 
                 FROM "LEAD_ACT" la
                 WHERE la."CREATIONDATE" >= ? AND la."CREATIONDATE" <= ? 
                   AND (UPPER(TRIM(la."STATUS")) = \'COMPLETED\' OR UPPER(TRIM(la."STATUS")) = \'FOLLOWUP\' OR UPPER(TRIM(la."STATUS")) = \'FOLLOW UP\')
                 GROUP BY CAST(la."CREATIONDATE" AS DATE), UPPER(TRIM(la."STATUS"))',
                [$queryStart, $queryEnd]
            );

            foreach ($actRows as $row) {
                $actDate = $row->ACT_DATE ?? $row->act_date ?? null;
                $actStatus = $row->ACT_STATUS ?? $row->act_status ?? '';
                $cnt = (int) ($row->CNT ?? $row->cnt ?? 0);

                if (! $actDate) {
                    continue;
                }

                $dateStr = Carbon::parse($actDate)->format('Y-m-d');
                $statusUpper = strtoupper(trim($actStatus));

                if ($statusUpper === 'COMPLETED') {
                    $key = 'COMPLETED';
                } elseif ($statusUpper === 'FOLLOWUP' || $statusUpper === 'FOLLOW UP') {
                    $key = 'FOLLOWUP';
                } else {
                    continue;
                }

                if (! isset($activityMap[$dateStr])) {
                    $activityMap[$dateStr] = ['COMPLETED' => 0, 'FOLLOWUP' => 0];
                }
                $activityMap[$dateStr][$key] += $cnt;
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to pre-fetch dashboard activity map: '.$e->getMessage());
        }

        // Pre-fetch lead creation map (past 180 days)
        $leadMap = [];
        try {
            $leadQueryStart = Carbon::today()->subDays(180)->format('Y-m-d 00:00:00');

            $leadRows = DB::select(
                'SELECT 
                    CAST(l."CREATEDAT" AS DATE) AS "CREATED_DATE",
                    COALESCE(ls."LATEST_STATUS", \'\') AS "LATEST_STATUS",
                    COUNT(*) AS "CNT"
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
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."CREATEDAT" >= ?
                 GROUP BY CAST(l."CREATEDAT" AS DATE), COALESCE(ls."LATEST_STATUS", \'\')',
                [$leadQueryStart]
            );

            foreach ($leadRows as $row) {
                $createdDate = $row->CREATED_DATE ?? $row->created_date ?? null;
                $latestStatus = strtoupper(trim((string) ($row->LATEST_STATUS ?? $row->latest_status ?? '')));
                $cnt = (int) ($row->CNT ?? $row->cnt ?? 0);

                if (! $createdDate || $latestStatus === 'CANCELLED') {
                    continue;
                }

                $dateStr = Carbon::parse($createdDate)->format('Y-m-d');
                $leadMap[$dateStr] = ($leadMap[$dateStr] ?? 0) + $cnt;
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to pre-fetch dashboard lead map: '.$e->getMessage());
        }

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
                     WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
                       AND l."CREATEDAT" <= ?
                       AND UPPER(TRIM(COALESCE(la."STATUS", \'\'))) <> \'CANCELLED\'
                       AND UPPER(TRIM(COALESCE(la."STATUS", \'\'))) IN (\'PENDING\', \'FOLLOWUP\', \'DEMO\', \'CONFIRMED\')',
                    [$cutoff, $cutoff]
                );

                return (int) ($row->c ?? $row->C ?? 0);
            };

            // In-memory WoW metric generation
            $temp = $startThisWeek->copy();
            $today = Carbon::today();
            while ($temp->lte($today)) {
                $dStr = $temp->format('Y-m-d');
                $leadsThisWeek += $leadMap[$dStr] ?? 0;
                $closedThisWeek += $activityMap[$dStr]['COMPLETED'] ?? 0;
                $referralThisWeek += $activityMap[$dStr]['FOLLOWUP'] ?? 0;
                $temp->addDay();
            }

            $temp = $startLastWeek->copy();
            while ($temp->lte($endLastWeek)) {
                $dStr = $temp->format('Y-m-d');
                $leadsLastWeek += $leadMap[$dStr] ?? 0;
                $closedLastWeek += $activityMap[$dStr]['COMPLETED'] ?? 0;
                $referralLastWeek += $activityMap[$dStr]['FOLLOWUP'] ?? 0;
                $temp->addDay();
            }

            $leadsUntilLastWeek = $totalLeads - $leadsThisWeek;
            $closedUntilLastWeek = $totalClosed - $closedThisWeek;
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

        $dealerStats = [];
        try {
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

            $dealerIds = array_map(fn ($d) => (string) ($d->USERID ?? ''), $topDealersRaw);
            $allStats = DealerStatsAggregator::getAllDealerStats($dealerIds);
            $allClosingTimes = DealerStatsAggregator::getAllDealerClosingTimes($dealerIds);

            $dealerStats = collect($topDealersRaw)->map(function ($d) use ($allStats, $allClosingTimes) {
                $userId = (string) ($d->USERID ?? '');
                $stats = $allStats[$userId] ?? ['totalLead' => 0, 'totalClosed' => 0, 'totalFailed' => 0, 'totalOngoing' => 0];
                $leads = $stats['totalLead'] ?? 0;
                $closed = $stats['totalClosed'] ?? 0;
                $failed = $stats['totalFailed'] ?? 0;
                $ongoing = $stats['totalOngoing'] ?? 0;
                $conversion = $leads > 0 ? ($closed / $leads) : 0;
                $avgClosingSeconds = $allClosingTimes[$userId] ?? null;

                $company = trim((string) ($d->COMPANY ?? ''));
                $alias = trim((string) ($d->ALIAS ?? ''));
                $email = trim((string) ($d->EMAIL ?? ''));
                $dealerName = $company !== '' ? $company : ($alias !== '' ? $alias : ($email !== '' ? $email : $userId));
                if (strcasecmp($company, 'E Stream Sdn Bhd') === 0 && $alias !== '') {
                    $dealerName = $company.'-'.$alias;
                }

                $avgClosingDisplay = DealerStatsAggregator::formatClosingTime($avgClosingSeconds);
                $postcode = trim((string) ($d->POSTCODE ?? ''));
                $city = trim((string) ($d->CITY ?? ''));
                $location = trim(trim($postcode.' '.$city));

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
                    if ($c !== 0) {
                        return $c;
                    }
                    $ta = $a['avg_closing_seconds'] ?? null;
                    $tb = $b['avg_closing_seconds'] ?? null;
                    $hasA = is_int($ta);
                    $hasB = is_int($tb);
                    if ($hasA && $hasB) {
                        $c2 = ($ta <=> $tb);
                        if ($c2 !== 0) {
                            return $c2;
                        }
                    } elseif ($hasA !== $hasB) {
                        return $hasA ? -1 : 1;
                    }
                    $c3 = ($b['closed_count'] <=> $a['closed_count']);
                    if ($c3 !== 0) {
                        return $c3;
                    }

                    return $b['total_leads'] <=> $a['total_leads'];
                })
                ->values()
                ->all();
        } catch (\Throwable $e) {
            \Log::warning('Dealer mapping generation failed: '.$e->getMessage());
        }

        // In-memory weekly chart labels and counts
        $chartLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $chartData = [0, 0, 0, 0, 0, 0, 0];
        $referralWeekData = [0, 0, 0, 0, 0, 0, 0];
        try {
            $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
            for ($i = 0; $i < 7; $i++) {
                $dayStr = $startOfWeek->copy()->addDays($i)->format('Y-m-d');
                $chartData[$i] = $activityMap[$dayStr]['COMPLETED'] ?? 0;
                $referralWeekData[$i] = $activityMap[$dayStr]['FOLLOWUP'] ?? 0;
            }
        } catch (\Throwable $e) {
            \Log::error('In-memory weekly chart population failed: '.$e->getMessage());
        }

        // In-memory monthly chart labels and counts
        $chartMonthLabels = [];
        $chartMonthData = [];
        $referralMonthData = [];
        try {
            $start = now()->startOfMonth();
            $daysInMonth = $start->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $chartMonthLabels[] = (string) $i;
                $dayStr = $start->copy()->day($i)->format('Y-m-d');
                $chartMonthData[] = $activityMap[$dayStr]['COMPLETED'] ?? 0;
                $referralMonthData[] = $activityMap[$dayStr]['FOLLOWUP'] ?? 0;
            }
        } catch (\Throwable $e) {
            \Log::error('In-memory monthly chart population failed: '.$e->getMessage());
        }

        // In-memory yearly chart labels and counts
        $chartYearLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $chartYearData = array_fill(0, 12, 0);
        $referralYearData = array_fill(0, 12, 0);
        try {
            $yearStart = now()->startOfYear();
            foreach ($activityMap as $dateStr => $counts) {
                $date = Carbon::parse($dateStr);
                if ($date->year === $yearStart->year) {
                    $monthIdx = $date->month - 1; // 0-11
                    $chartYearData[$monthIdx] += $counts['COMPLETED'] ?? 0;
                    $referralYearData[$monthIdx] += $counts['FOLLOWUP'] ?? 0;
                }
            }
        } catch (\Throwable $e) {
            \Log::error('In-memory yearly chart population failed: '.$e->getMessage());
        }

        // In-memory 30/60/90-day range calculations
        $dashboardClosedCaseRanges = [];
        $dashboardReferralRanges = [];
        $dashboardReferralRangeChanges = [];
        $dashboardMetricRangeChanges = [];

        foreach ([30, 60, 90] as $rangeDays) {
            $rangeKey = (string) $rangeDays;
            $labels = [];
            $closedData = [];
            $referralData = [];
            $tooltipTitles = [];

            $rangeEnd = Carbon::today();
            $rangeStart = $rangeEnd->copy()->subDays($rangeDays - 1);

            for ($d = 0; $d < $rangeDays; $d++) {
                $day = $rangeStart->copy()->addDays($d);
                $dayStr = $day->format('Y-m-d');
                $labels[] = $day->format('d M');
                $tooltipTitles[] = $day->format('d/m/Y');

                $closedData[] = $activityMap[$dayStr]['COMPLETED'] ?? 0;
                $referralData[] = $activityMap[$dayStr]['FOLLOWUP'] ?? 0;
            }

            // Previous period for comparison
            $prevEnd = $rangeStart->copy()->subDay();
            $prevStart = $prevEnd->copy()->subDays($rangeDays - 1);

            // Sum current period
            $curClosed = 0;
            $curRef = 0;
            $temp = $rangeStart->copy();
            while ($temp->lte($rangeEnd)) {
                $dStr = $temp->format('Y-m-d');
                $curClosed += $activityMap[$dStr]['COMPLETED'] ?? 0;
                $curRef += $activityMap[$dStr]['FOLLOWUP'] ?? 0;
                $temp->addDay();
            }

            // Sum previous period
            $prevClosed = 0;
            $prevRef = 0;
            $temp = $prevStart->copy();
            while ($temp->lte($prevEnd)) {
                $dStr = $temp->format('Y-m-d');
                $prevClosed += $activityMap[$dStr]['COMPLETED'] ?? 0;
                $prevRef += $activityMap[$dStr]['FOLLOWUP'] ?? 0;
                $temp->addDay();
            }

            $dashboardReferralRangeChanges[$rangeKey] = $percentChange($curRef, $prevRef);

            // Sum leads in current/previous range
            $curLeadsCount = 0;
            $temp = $rangeStart->copy();
            while ($temp->lte($rangeEnd)) {
                $curLeadsCount += $leadMap[$temp->format('Y-m-d')] ?? 0;
                $temp->addDay();
            }

            $prevLeadsCount = 0;
            $temp = $prevStart->copy();
            while ($temp->lte($prevEnd)) {
                $prevLeadsCount += $leadMap[$temp->format('Y-m-d')] ?? 0;
                $temp->addDay();
            }

            $dashboardMetricRangeChanges[$rangeKey] = [
                'leads' => $percentChange($curLeadsCount, $prevLeadsCount),
                'closed' => $percentChange($curClosed, $prevClosed),
                'active' => $percentChange($activeInquiries, max($activeInquiries - ($curLeadsCount - $prevLeadsCount), 0)),
                'conversion' => $totalLeads > 0
                    ? round(($curClosed / max($curLeadsCount, 1)) * 100 - ($prevClosed / max($prevLeadsCount, 1)) * 100, 1)
                    : 0.0,
            ];

            $dashboardClosedCaseRanges[$rangeKey] = [
                'labels' => $labels,
                'data' => $closedData,
                'tooltipTitles' => $tooltipTitles,
            ];
            $dashboardReferralRanges[$rangeKey] = [
                'labels' => $labels,
                'data' => $referralData,
                'tooltipTitles' => $tooltipTitles,
            ];
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
            'topDealers' => $dealerStats,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'chartMonthLabels' => $chartMonthLabels,
            'chartMonthData' => $chartMonthData,
            'chartYearLabels' => $chartYearLabels,
            'chartYearData' => $chartYearData,
            'referralWeekData' => $referralWeekData,
            'referralMonthData' => $referralMonthData,
            'referralYearData' => $referralYearData,
            'dashboardClosedCaseRanges' => $dashboardClosedCaseRanges,
            'dashboardReferralRanges' => $dashboardReferralRanges,
            'dashboardReferralRangeChanges' => $dashboardReferralRangeChanges,
            'dashboardMetricRangeChanges' => $dashboardMetricRangeChanges,
        ];
    }

    public function dashboard(): View
    {
        return view('admin.dashboard', array_merge($this->dashboardData(), ['currentPage' => 'dashboard']));
    }

    public function notifications(): JsonResponse
    {
        try {
            $rows = DB::select(
                'SELECT FIRST 10
                    "LEADID", "COMPANYNAME", "CONTACTNAME", "CREATEDAT", "DESCRIPTION"
                 FROM "LEAD"
                 WHERE COALESCE("ISDELETED", FALSE) = FALSE
                   AND ("ASSIGNEDTO" IS NULL OR TRIM("ASSIGNEDTO") = \'\')
                 ORDER BY "LEADID" DESC'
            );

            $items = array_map(function ($r) {
                $leadId = (int) ($r->LEADID ?? 0);
                $company = trim((string) ($r->COMPANYNAME ?? ''));
                $contact = trim((string) ($r->CONTACTNAME ?? ''));
                $createdAt = $r->CREATEDAT ?? '';
                $desc = trim((string) ($r->DESCRIPTION ?? ''));

                $title = $company ?: $contact ?: ('Inquiry #SQL-'.$leadId);
                if ($company && $contact) {
                    $title = $company.' ('.$contact.')';
                }

                return [
                    'id' => $leadId,
                    'lead_id' => $leadId,
                    'title' => $title,
                    'description' => Str::limit($desc, 60),
                    'time' => $createdAt ? Carbon::parse($createdAt)->diffForHumans() : '',
                    'target_url' => route('admin.inquiries', ['lead' => $leadId]),
                ];
            }, $rows);

            return response()->json(['items' => $items]);
        } catch (\Throwable $e) {
            return response()->json(['items' => [], 'error' => $e->getMessage()], 500);
        }
    }

    public function inquiries(Request $request): View
    {
        $focusLeadId = (int) $request->query('lead', 0);

        $rows = DB::select(
            'SELECT
                "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL","ADDRESS1","ADDRESS2","CITY","STATE","COUNTRY","POSTCODE",
                "BUSINESSNATURE","USERCOUNT","EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                "CREATEDAT","CREATEDBY","ASSIGNEDTO" AS "assignedTo","LASTMODIFIED"
            FROM "LEAD"
            WHERE COALESCE("ISDELETED", FALSE) = FALSE
            ORDER BY "LEADID" DESC'
        );

        if ($focusLeadId > 0) {
            $found = false;
            foreach ($rows as $r) {
                if ((int) ($r->LEADID ?? 0) === $focusLeadId) {
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $focusRow = DB::selectOne(
                    'SELECT
                        "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL","ADDRESS1","ADDRESS2","CITY","STATE","COUNTRY","POSTCODE",
                        "BUSINESSNATURE","USERCOUNT","EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                        "CREATEDAT","CREATEDBY","ASSIGNEDTO" AS "assignedTo","LASTMODIFIED"
                    FROM "LEAD"
                    WHERE "LEADID" = ? AND COALESCE("ISDELETED", FALSE) = FALSE',
                    [$focusLeadId]
                );
                if ($focusRow) {
                    array_unshift($rows, $focusRow);
                }
            }
        }

        // Derive CURRENTSTATUS from latest LEAD_ACT status per LEADID
        try {
            $leadIds = [];
            foreach ($rows as $r) {
                $lid = (int) ($r->LEADID ?? 0);
                if ($lid > 0) {
                    $leadIds[$lid] = true;
                }
            }
            $leadIds = array_keys($leadIds);
            if (! empty($leadIds)) {
                $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
                $acts = DB::select(
                    'SELECT a."LEADID", a."STATUS"
                     FROM "LEAD_ACT" a
                     JOIN (
                         SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                         FROM "LEAD_ACT"
                         WHERE "LEADID" IN ('.$placeholders.')
                         GROUP BY "LEADID"
                     ) x
                       ON x."LEADID" = a."LEADID" AND x.MAXCD = a."CREATIONDATE"
                     WHERE a."LEADID" IN ('.$placeholders.')',
                    array_merge($leadIds, $leadIds)
                );
                $statusMap = [];
                foreach ($acts as $a) {
                    $lid = (int) ($a->LEADID ?? 0);
                    if ($lid > 0) {
                        $statusMap[$lid] = trim((string) ($a->STATUS ?? ''));
                    }
                }
                foreach ($rows as $r) {
                    $lid = (int) ($r->LEADID ?? 0);
                    if ($lid > 0 && isset($statusMap[$lid]) && $statusMap[$lid] !== '') {
                        $r->CURRENTSTATUS = $statusMap[$lid];
                    } else {
                        $r->CURRENTSTATUS = 'Created';
                    }
                }
            }
        } catch (\Throwable $e) {
            // If LEAD_ACT lookup fails, default to Created
            foreach ($rows as $r) {
                if (! isset($r->CURRENTSTATUS)) {
                    $r->CURRENTSTATUS = 'Created';
                }
            }
        }

        $unassigned = [];
        $assigned = [];
        foreach ($rows as $r) {
            $assignedTo = trim((string) ($r->assignedTo ?? ''));
            $leadStatus = strtoupper(trim((string) ($r->CURRENTSTATUS ?? '')));
            if ($assignedTo === '' && in_array($leadStatus, ['OPEN', 'CREATED'], true)) {
                $unassigned[] = $r;
            } elseif ($assignedTo !== '') {
                $assigned[] = $r;
            }
        }

        // Sort unassigned and assigned
        usort($unassigned, function ($a, $b) {
            $ta = strtotime($a->CREATEDAT ?? '0');
            $tb = strtotime($b->CREATEDAT ?? '0');

            return $tb <=> $ta;
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
                $to = trim((string) ($r->assignedTo ?? ''));
                $by = trim((string) ($r->CREATEDBY ?? ''));
                if ($to !== '') {
                    $ids[$to] = true;
                }
                if ($by !== '') {
                    $ids[$by] = true;
                }
                $leadId = (int) ($r->LEADID ?? 0);
                $assignerId = $leadId > 0 ? trim((string) ($assignmentByLeadMap[$leadId] ?? '')) : '';
                if ($assignerId !== '') {
                    $ids[$assignerId] = true;
                }
            }
            $maps = $this->userDisplayMaps(array_keys($ids));
            $assignedToMap = $maps['assignedToMap'];
            $actorMap = $maps['actorMap'];
            if (! empty($assignedToMap) || ! empty($actorMap)) {
                foreach ($rows as $r) {
                    $to = trim((string) ($r->assignedTo ?? ''));
                    $by = trim((string) ($r->CREATEDBY ?? ''));
                    $leadId = (int) ($r->LEADID ?? 0);
                    $assignerId = $leadId > 0 ? trim((string) ($assignmentByLeadMap[$leadId] ?? '')) : '';
                    if ($to !== '' && isset($assignedToMap[$to])) {
                        $r->assignedToName = $assignedToMap[$to];
                    }
                    if ($by !== '' && isset($actorMap[$by])) {
                        $r->CREATEDBY_NAME = $actorMap[$by];
                    }
                    if ($assignerId !== '') {
                        $r->ASSIGNEDBY = $assignerId;
                    }
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
            if (! in_array($status, ['COMPLETED', 'CASE COMPLETED', 'FAILED', 'REWARDED', 'REWARD', 'REWARD DISTRIBUTED', 'CANCELLED'], true)) {
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
                        TRIM(CAST(l."ASSIGNEDTO" AS VARCHAR(50))) AS UID,
                        COUNT(*) AS TOTAL_LEAD,
                        SUM(CASE WHEN ls."LATEST_STATUS" IN (\'COMPLETED\', \'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\') THEN 1 ELSE 0 END) AS TOTAL_CLOSED
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
                     WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
                       AND l."ASSIGNEDTO" IS NOT NULL AND TRIM(CAST(l."ASSIGNEDTO" AS VARCHAR(50))) <> \'\'
                       AND COALESCE(ls."LATEST_STATUS", \'\') <> \'CANCELLED\'
                     GROUP BY TRIM(CAST(l."ASSIGNEDTO" AS VARCHAR(50)))'
                );
                foreach ($statsRows as $sr) {
                    $uid = trim((string) ($sr->UID ?? $sr->uid ?? ''));
                    if ($uid === '') {
                        continue;
                    }
                    $totalLead = (int) ($sr->TOTAL_LEAD ?? $sr->total_lead ?? 0);
                    $totalClosed = (int) ($sr->TOTAL_CLOSED ?? $sr->total_closed ?? 0);
                    $leadStats[$uid] = [
                        'totalLead' => $totalLead,
                        'totalClosed' => $totalClosed,
                    ];
                }
            } catch (\Throwable $e) {
                // leave stats empty
            }

            $dealers = array_map(function ($r) use ($leadStats) {
                $uid = trim((string) ($r->USERID ?? ''));
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

        $duplicateGroups = [];
        try {
            $dupKeys = DB::select(
                'SELECT LOWER(TRIM("COMPANYNAME")) AS "comp", LOWER(TRIM("CONTACTNO")) AS "phone", LOWER(TRIM("EMAIL")) AS "email_addr", COUNT(*) AS "cnt"
                 FROM "LEAD"
                 WHERE COALESCE("ISDELETED", FALSE) = FALSE
                 GROUP BY LOWER(TRIM("COMPANYNAME")), LOWER(TRIM("CONTACTNO")), LOWER(TRIM("EMAIL"))
                 HAVING COUNT(*) > 1'
            );

            if (! empty($dupKeys)) {
                foreach ($dupKeys as $k) {
                    $comp = $k->comp ?? '';
                    $phone = $k->phone ?? '';
                    $email_addr = $k->email_addr ?? '';

                    $leads = DB::select(
                        'SELECT "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL",
                                "ADDRESS1","ADDRESS2","CITY","STATE","COUNTRY","POSTCODE",
                                "BUSINESSNATURE","USERCOUNT","EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION",
                                "REFERRALCODE","CREATEDAT","ASSIGNEDTO"
                         FROM "LEAD"
                         WHERE COALESCE("ISDELETED", FALSE) = FALSE
                           AND LOWER(TRIM("COMPANYNAME")) = ?
                           AND LOWER(TRIM("CONTACTNO")) = ?
                           AND LOWER(TRIM("EMAIL")) = ?
                         ORDER BY "LEADID" DESC',
                        [$comp, $phone, $email_addr]
                    );

                    // Check if there is at least one unassigned lead in the duplicate group
                    $hasUnassigned = false;
                    $forceInclude = false;
                    foreach ($leads as $l) {
                        if (empty(trim((string) ($l->ASSIGNEDTO ?? '')))) {
                            $hasUnassigned = true;
                        }
                        if (session('auto_resolve_duplicate') && $l->LEADID == session('auto_resolve_duplicate')) {
                            $forceInclude = true;
                        }
                    }

                    if (($hasUnassigned || $forceInclude) && count($leads) > 1) {
                        $duplicateGroups[] = [
                            'company' => $leads[0]->COMPANYNAME,
                            'phone' => $leads[0]->CONTACTNO,
                            'email' => $leads[0]->EMAIL,
                            'leads' => $leads,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $assignedPerPage = 10;
        $assignedTotal = count($assigned);
        $assignedForView = $assigned;
        $assignedLastPage = $assignedTotal > 0 ? (int) ceil($assignedTotal / $assignedPerPage) : 1;
        $allRows = $rows;
        $allPerPage = 10;
        $allTotal = count($allRows);

        $duplicateLeadIds = [];
        foreach ($duplicateGroups as $group) {
            $groupLeads = $group['leads'];
            usort($groupLeads, function ($a, $b) {
                return (int) $a->LEADID <=> (int) $b->LEADID;
            });
            for ($i = 1; $i < count($groupLeads); $i++) {
                $duplicateLeadIds[(int) $groupLeads[$i]->LEADID] = true;
            }
        }

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
            'focusLeadId' => $focusLeadId,
            'duplicateGroups' => $duplicateGroups,
            'duplicateLeadIds' => $duplicateLeadIds,
        ]);
    }

    public function inquiriesAssignedPage(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 10;

        $rows = DB::select(
            'SELECT
                "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL","ADDRESS1","ADDRESS2","CITY","STATE","COUNTRY","POSTCODE",
                "BUSINESSNATURE","USERCOUNT","EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                "CREATEDAT","CREATEDBY","ASSIGNEDTO" AS "assignedTo","LASTMODIFIED"
            FROM "LEAD"
            WHERE COALESCE("ISDELETED", FALSE) = FALSE
            ORDER BY "LEADID" DESC'
        );
        $assigned = [];
        foreach ($rows as $r) {
            if (trim((string) ($r->assignedTo ?? '')) !== '') {
                $assigned[] = $r;
            }
        }
        usort($assigned, function ($a, $b) {
            $ta = strtotime($a->LASTMODIFIED ?? $a->CREATEDAT ?? '0');
            $tb = strtotime($b->LASTMODIFIED ?? $b->CREATEDAT ?? '0');

            return $tb <=> $ta;
        });

        $leadIds = array_values(array_unique(array_filter(array_map(function ($r) {
            return (int) ($r->LEADID ?? 0);
        }, $rows))));
        if (! empty($leadIds)) {
            $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
            try {
                $acts = DB::select(
                    'SELECT a."LEADID", a."STATUS"
                     FROM "LEAD_ACT" a
                     JOIN (
                         SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                         FROM "LEAD_ACT"
                         WHERE "LEADID" IN ('.$placeholders.')
                         GROUP BY "LEADID"
                     ) x
                       ON x."LEADID" = a."LEADID" AND x.MAXCD = a."CREATIONDATE"
                     WHERE a."LEADID" IN ('.$placeholders.')',
                    array_merge($leadIds, $leadIds)
                );
                $statusMap = [];
                foreach ($acts as $a) {
                    $lid = (int) ($a->LEADID ?? 0);
                    if ($lid > 0) {
                        $statusMap[$lid] = trim((string) ($a->STATUS ?? ''));
                    }
                }
                foreach ($rows as $r) {
                    $lid = (int) ($r->LEADID ?? 0);
                    $r->CURRENTSTATUS = ($lid > 0 && isset($statusMap[$lid]) && $statusMap[$lid] !== '')
                        ? $statusMap[$lid]
                        : 'Created';
                }
            } catch (\Throwable $e) {
                foreach ($rows as $r) {
                    if (! isset($r->CURRENTSTATUS)) {
                        $r->CURRENTSTATUS = 'Created';
                    }
                }
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
                $to = trim((string) ($r->assignedTo ?? ''));
                $by = trim((string) ($r->CREATEDBY ?? ''));
                if ($to !== '') {
                    $ids[$to] = true;
                }
                if ($by !== '') {
                    $ids[$by] = true;
                }
                $leadId = (int) ($r->LEADID ?? 0);
                $assignerId = $leadId > 0 ? trim((string) ($assignmentByLeadMap[$leadId] ?? '')) : '';
                if ($assignerId !== '') {
                    $ids[$assignerId] = true;
                }
            }
            $maps = $this->userDisplayMaps(array_keys($ids));
            $assignedToMap = $maps['assignedToMap'];
            $actorMap = $maps['actorMap'];
            foreach ($rows as $r) {
                $to = trim((string) ($r->assignedTo ?? ''));
                $by = trim((string) ($r->CREATEDBY ?? ''));
                $leadId = (int) ($r->LEADID ?? 0);
                $assignerId = $leadId > 0 ? trim((string) ($assignmentByLeadMap[$leadId] ?? '')) : '';
                if ($to !== '' && isset($assignedToMap[$to])) {
                    $r->assignedToName = $assignedToMap[$to];
                }
                if ($by !== '' && isset($actorMap[$by])) {
                    $r->CREATEDBY_NAME = $actorMap[$by];
                }
                if ($assignerId !== '') {
                    $r->ASSIGNEDBY = $assignerId;
                }
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

        $duplicateLeadIds = $this->getDuplicateLeadIds();

        $html = view('admin.partials.inquiries_assigned_rows', [
            'assigned' => $assignedSlice,
            'productLabels' => $productLabels,
            'duplicateLeadIds' => $duplicateLeadIds,
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
            'assignedTo' => 'required',
        ]);

        $leadId = (int) $validated['LEADID'];
        $assignedTo = trim((string) $validated['assignedTo']);
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
            if (! $assignee) {
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
                'SELECT "ASSIGNEDTO" AS "assignedTo", "LASTMODIFIED" FROM "LEAD" WHERE "LEADID" = ?',
                [$leadId]
            );
            if ($current) {
                $prevAssignedTo = trim((string) ($current->assignedTo ?? ''));
                $prevLastModified = $current->LASTMODIFIED ?? null;
            }
        } catch (\Throwable $e) {
            $prevAssignedTo = null;
            $prevLastModified = null;
        }

        try {
            DB::beginTransaction();
            $updated = DB::update(
                'UPDATE "LEAD"
                 SET "ASSIGNEDTO" = ?, "LASTMODIFIED" = CURRENT_TIMESTAMP
                 WHERE "LEADID" = ?
                   AND ("ASSIGNEDTO" IS NULL OR TRIM(CAST("ASSIGNEDTO" AS VARCHAR(50))) = \'\')',
                [$assignedTo, $leadId]
            );
            if ((int) $updated < 1) {
                $currentAssignedRow = DB::selectOne(
                    'SELECT "ASSIGNEDTO" AS "assignedTo" FROM "LEAD" WHERE "LEADID" = ?',
                    [$leadId]
                );
                DB::rollBack();

                $currentAssigned = trim((string) ($currentAssignedRow->assignedTo ?? ''));
                if ($currentAssigned !== '') {
                    $maps = $this->userDisplayMaps([$currentAssigned]);
                    $assignedToMap = $maps['assignedToMap'] ?? [];
                    $currentAssignedLabel = $assignedToMap[$currentAssigned] ?? $currentAssigned;

                    return back()->with('error', 'This inquiry is already assigned to '.$currentAssignedLabel.'. Please sync and try again.');
                }

                return back()->with('error', 'This inquiry was updated by another user. Please sync and try again.');
            }

            // Insert "Lead Assigned" activity (replaces TRD_LEAD_AFTER_UPDATE_ASSIGN trigger)
            $createdByUser = $fromUserId !== '' ? $fromUserId : $assignedTo;
            DB::insert(
                'INSERT INTO "LEAD_ACT" ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS")
                 VALUES (NEXT VALUE FOR GEN_LEAD_ACTID,?,?,CURRENT_TIMESTAMP,?,?,NULL,?)',
                [$leadId, $createdByUser, 'Lead Assigned', 'Lead Assigned by '.$createdByUser.' to '.$assignedTo, 'Pending']
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Could not assign lead. Please try again.');
        }

        try {
            $this->sendInquiryAssignedEmail($assignedTo, $leadId);
        } catch (\Throwable $e) {
            \Log::error('Failed to send assignment email: ' . $e->getMessage());
        }

        return redirect()->route('admin.inquiries')
            ->with('success', 'Lead assigned successfully.');
    }

    /**
     * Send dealer assignment email. Called by frontend 6 seconds after assign (after undo window).
     * Only sends if the lead is still assigned to the given dealer (undo was not clicked).
     */
    public function sendAssignmentEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => 'required|integer|min:1',
            'assignedTo' => 'required|string|max:50',
        ]);
        $leadId = (int) $validated['lead_id'];
        $assignedTo = trim((string) $validated['assignedTo']);

        $row = DB::selectOne('SELECT "ASSIGNEDTO" AS "assignedTo" FROM "LEAD" WHERE "LEADID" = ?', [$leadId]);
        if (! $row) {
            return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
        }
        $currentAssigned = trim((string) ($row->assignedTo ?? ''));
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
            'prevAssignedTo' => 'nullable|string|max:50',
            'PREV_LASTMODIFIED' => 'nullable|string|max:50',
        ]);
        $leadId = (int) $validated['LEADID'];
        $prev = trim((string) ($validated['prevAssignedTo'] ?? ''));
        $prevLastModified = trim((string) ($validated['PREV_LASTMODIFIED'] ?? ''));

        try {
            DB::beginTransaction();

            $assignedTo = $prev !== '' ? $prev : null;
            $restoredLastModified = $prevLastModified !== '' ? $prevLastModified : null;

            if ($prev === '') {
                $updated = DB::update(
                    'UPDATE "LEAD"
                     SET "ASSIGNEDTO" = ?, "LASTMODIFIED" = ?
                     WHERE "LEADID" = ?',
                    [$assignedTo, $restoredLastModified, $leadId]
                );
            } else {
                $updated = DB::update(
                    'UPDATE "LEAD"
                     SET "ASSIGNEDTO" = ?, "LASTMODIFIED" = ?
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
                       SET "LASTMODIFIED" = ?
                       WHERE "LEADID" = ?',
                    [$restoredLastModified, $leadId]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->route('admin.inquiries')->with('error', 'Could not undo assignment. Please try again.');
        }

        return redirect()->route('admin.inquiries')->with('success', 'Assignment undone.');
    }

    public function markInquiryCancelled(Request $request): RedirectResponse
    {
        $leadId = (int) $request->input('lead_id');
        $message = trim((string) $request->input('FAIL_REASON', ''));

        if ($message === '') {
            $message = trim((string) $request->input('reason', 'Marked as Cancelled by Admin.'));
        }

        if ($leadId <= 0) {
            return back()->with('error', 'Invalid inquiry specified.');
        }

        $userId = trim((string) ($request->session()->get('user_id') ?? ''));

        $lead = DB::selectOne(
            'SELECT "LEADID" FROM "LEAD" WHERE "LEADID" = ?',
            [$leadId]
        );

        if (! $lead) {
            return back()->with('error', 'Lead not found.');
        }

        $currentStatusRow = DB::selectOne(
            'SELECT FIRST 1 UPPER(TRIM("STATUS")) AS "CURRENT_STATUS"
             FROM "LEAD_ACT"
             WHERE "LEADID" = ?
             ORDER BY "CREATIONDATE" DESC, "LEAD_ACTID" DESC',
            [$leadId]
        );

        $currentStatus = $currentStatusRow->CURRENT_STATUS ?? '';

        if (in_array($currentStatus, ['COMPLETED', 'REWARDED', 'FAILED', 'CANCELLED'], true)) {
            return back()->with('error', 'Cannot mark as Cancelled: lead is already '.$currentStatus.'.');
        }

        DB::beginTransaction();
        try {
            DB::update(
                'UPDATE "LEAD"
                 SET "LASTMODIFIED" = CURRENT_TIMESTAMP
                 WHERE "LEADID" = ?',
                [$leadId]
            );

            DB::insert(
                'INSERT INTO "LEAD_ACT"
                    ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS")
                 VALUES (NEXT VALUE FOR GEN_LEAD_ACTID,?,?,CURRENT_TIMESTAMP,?,?,?,?)',
                [$leadId, $userId !== '' ? $userId : null, 'Status changed to Cancelled', $message, null, 'Cancelled']
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Admin mark inquiry cancelled failed: '.$e->getMessage());

            return back()->with('error', 'Could not mark the inquiry as cancelled. Please try again.');
        }

        return redirect()->route('admin.inquiries', ['tab' => 'assigned'])->with('success', 'Lead marked as Cancelled.');
    }

    public function leadStatus(int $leadId): \Illuminate\Http\JsonResponse
    {
        $rows = DB::select(
            'SELECT "LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS","SERIALACC","SERIALPAY","ATTACHMENT2"
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
            if (! empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $users = DB::select(
                    'SELECT "USERID","SYSTEMROLE","ALIAS","COMPANY","EMAIL"
                     FROM "USERS"
                     WHERE CAST("USERID" AS VARCHAR(50)) IN ('.$placeholders.')',
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
                        $userNameMap[$uid] = $role.'- '.$alias;
                    } elseif ($role !== '') {
                        $userNameMap[$uid] = $role.'- '.($company !== '' ? $company : ($email !== '' ? $email : $uid));
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
            if (! empty($r->CREATIONDATE)) {
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
                'serial_acc' => trim((string) ($r->SERIALACC ?? '')),
                'serial_pay' => trim((string) ($r->SERIALPAY ?? '')),
                'created_at' => $createdAtIso,
                'attachment_urls' => AttachmentUrlBuilder::buildUrls(
                    $r->ATTACHMENT ?? null,
                    (int) ($r->LEADID ?? $leadId),
                    (int) ($r->LEAD_ACTID ?? 0),
                    'admin.rewards.serve-attachment',
                    'admin.rewards.activity-attachment'
                ),
                'attachment2_urls' => AttachmentUrlBuilder::buildUrls(
                    $r->ATTACHMENT2 ?? null,
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
            'SERIALACC' => $r->SERIALACC,
            'SERIALPAY' => $r->SERIALPAY,
        ], $rows);

        return response()->json([
            'items' => $items,
            'activities' => $activities,
        ]);
    }

    public function checkDuplicatesAjax(Request $request): JsonResponse
    {
        $comp = strtolower(trim((string) $request->input('COMPANYNAME', '')));
        $leadId = $request->input('LEADID');

        if ($comp === '') {
            return response()->json(['has_duplicates' => false]);
        }

        $leads = DB::select(
            'SELECT "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL",
                    "ADDRESS1","ADDRESS2","CITY","STATE","COUNTRY","POSTCODE",
                    "BUSINESSNATURE","USERCOUNT","EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION",
                    "REFERRALCODE","CREATEDAT","ASSIGNEDTO"
             FROM "LEAD"
             WHERE COALESCE("ISDELETED", FALSE) = FALSE
               AND LOWER(TRIM("COMPANYNAME")) = ?
             ORDER BY "LEADID" DESC',
            [$comp]
        );

        $otherLeadsCount = 0;
        foreach ($leads as $l) {
            if ($leadId === null || $l->LEADID != $leadId) {
                $otherLeadsCount++;
            }
        }

        if ($otherLeadsCount > 0) {
            $group = [
                'company' => $leads[0]->COMPANYNAME,
                'phone' => $leads[0]->CONTACTNO,
                'email' => $leads[0]->EMAIL,
                'leads' => $leads,
            ];
            
            $html = view('admin.partials.resolve_duplicates_modal_standalone', [
                'group' => $group,
                'groupIdx' => 0
            ])->render();

            return response()->json([
                'has_duplicates' => true,
                'html' => $html
            ]);
        }

        return response()->json(['has_duplicates' => false]);
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
                    "ADDRESS1","ADDRESS2","CITY","STATE","COUNTRY","POSTCODE","BUSINESSNATURE",
                    "EXISTINGSOFTWARE","USERCOUNT","DEMOMODE"
                 FROM "LEAD"
                 WHERE COALESCE("ISDELETED", FALSE) = FALSE
                   AND UPPER(TRIM("COMPANYNAME")) = UPPER(TRIM(?))
                 ORDER BY "LEADID" DESC',
                [$name]
            );
            if (! $row) {
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
                'state' => (string) ($row->STATE ?? ''),
                'country' => (string) ($row->COUNTRY ?? ''),
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
        // Allow editing assigned/in-progress inquiries
        $checkExist = DB::selectOne('SELECT "LEADID" FROM "LEAD" WHERE "LEADID" = ? AND COALESCE("ISDELETED", FALSE) = FALSE', [$leadId]);
        if (! $checkExist) {
            return redirect()->route('admin.inquiries')->with('error', 'Lead not found.');
        }

        $row = DB::selectOne(
            'SELECT "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL",
                "ADDRESS1","ADDRESS2","CITY","STATE","COUNTRY","POSTCODE","BUSINESSNATURE","USERCOUNT",
                "EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE","ASSIGNEDTO",
                COALESCE("LASTMODIFIED", "CREATEDAT") AS "SNAPSHOT_MODIFIED_AT"
             FROM "LEAD" WHERE "LEADID" = ?',
            [$leadId]
        );
        if (! $row) {
            return redirect()->route('admin.inquiries')->with('error', 'Lead not found.');
        }

        return view('admin.inquiries-create', $this->inquiryFormViewData($row));
    }

    public function storeInquiry(Request $request): RedirectResponse
    {
        $country = trim((string) $request->input('COUNTRY', ''));
        $isMY = ($country === 'MY' || $country === 'Malaysia' || $country === '');

        $validated = $request->validate(
            [
                'COMPANYNAME' => 'required|string|max:50',
                'CONTACTNAME' => 'required|string|max:100',
                'CONTACTNO' => 'required|string|max:50',
                'EMAIL' => 'required|email|max:255',
                'ADDRESS1' => 'nullable|string|max:255',
                'ADDRESS2' => 'nullable|string|max:255',
                'CITY' => $isMY ? 'required|string|max:100' : 'nullable|string|max:100',
                'STATE' => 'nullable|string|max:100',
                'COUNTRY' => 'nullable|string|max:100',
                'POSTCODE' => $isMY ? 'required|string|digits:5' : 'nullable|string|max:20',
                'BUSINESSNATURE' => 'required|string|max:50',
                'USERCOUNT' => 'nullable|integer',
                'EXISTINGSOFTWARE' => 'required|string|max:40',
                'DEMOMODE' => 'required|string|in:Zoom,On-site',
                'product_interested' => 'required|array',
                'product_interested.*' => 'integer|in:1,2,3,4,5,6,7,8,9,10,11',
                'DESCRIPTION' => 'nullable|string|max:4000',
                'REFERRALCODE' => 'nullable|string|max:100',
                'assignedTo' => 'nullable|string|max:50',
            ],
            [
                'POSTCODE.digits' => 'Invalid PostCode.',
                'product_interested.*' => 'Please select at least one product.',
                'product_interested.min' => 'Please select at least one product.',
                'product_interested.required' => 'Please select at least one product.',
            ],
            [
                'CONTACTNO' => 'Contact no',
                'POSTCODE' => 'Post code',
            ]
        );

        // Soft-check for existing lead with the same company name (case-insensitive).
        // First submit: show a friendly warning; second submit with duplicate_ok=1: proceed.
        if (! $request->boolean('duplicate_ok')) {
            try {
                $existing = DB::selectOne(
                    'SELECT FIRST 1 l."LEADID",l."COMPANYNAME",l."CONTACTNAME",l."EMAIL",
                        COALESCE(
                            (SELECT FIRST 1 la."STATUS"
                             FROM "LEAD_ACT" la
                             WHERE la."LEADID" = l."LEADID"
                             ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                            \'Created\'
                        ) AS "CURRENTSTATUS",
                        l."CREATEDAT"
                     FROM "LEAD" l
                     WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
                       AND UPPER(TRIM(l."COMPANYNAME")) = UPPER(TRIM(?))
                     ORDER BY
                        CASE
                            WHEN UPPER(TRIM(COALESCE(
                                (SELECT FIRST 1 la2."STATUS"
                                 FROM "LEAD_ACT" la2
                                 WHERE la2."LEADID" = l."LEADID"
                                 ORDER BY la2."CREATIONDATE" DESC, la2."LEAD_ACTID" DESC),
                                \'\'
                            ))) = \'FAILED\' THEN 1
                            ELSE 0
                        END ASC,
                        COALESCE(l."LASTMODIFIED", l."CREATEDAT") DESC,
                        l."LEADID" DESC',
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
                            $parts[] = 'Lead #SQL-'.$leadId;
                        }
                        if ($createdLabel) {
                            $parts[] = 'was created on '.$createdLabel;
                        }
                        if ($status !== '') {
                            $parts[] = 'with status '.$status;
                        }
                        $line2 = $parts ? implode(' ', $parts).'.' : '';
                        $message = trim($line1."\n\n".$line2);

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
            DB::beginTransaction();
            $newLeadRow = DB::selectOne(
                'INSERT INTO "LEAD" (
                    "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL",
                    "ADDRESS1","ADDRESS2","CITY","STATE","COUNTRY","POSTCODE","BUSINESSNATURE","USERCOUNT",
                    "EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                    "CREATEDAT","CREATEDBY","ASSIGNEDTO","LASTMODIFIED"
                ) VALUES (GEN_ID(GEN_LEADID, 1),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,?,?,CURRENT_TIMESTAMP)
                RETURNING "LEADID"',
                [
                    $productIdValue,
                    $validated['COMPANYNAME'],
                    $validated['CONTACTNAME'],
                    $validated['CONTACTNO'],
                    $validated['EMAIL'],
                    $validated['ADDRESS1'] ?? null,
                    $validated['ADDRESS2'] ?? null,
                    $validated['CITY'] ?? null,
                    $validated['STATE'] ?? null,
                    $validated['COUNTRY'] ?? null,
                    $validated['POSTCODE'] ?? null,
                    $validated['BUSINESSNATURE'],
                    $validated['USERCOUNT'] ?? null,
                    $validated['EXISTINGSOFTWARE'],
                    $validated['DEMOMODE'],
                    $descriptionValue,
                    $validated['REFERRALCODE'] ?? null,
                    $userId,
                    $validated['assignedTo'] ?? null,
                ]
            );
            $newLeadId = (int) ($newLeadRow->LEADID ?? $newLeadRow->leadid ?? 0);

            // Insert initial "Lead Created" activity (replaces TRD_LEAD_AFTER_INSERT trigger)
            if ($newLeadId > 0) {
                DB::insert(
                    'INSERT INTO "LEAD_ACT" ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS")
                     VALUES (NEXT VALUE FOR GEN_LEAD_ACTID,?,?,CURRENT_TIMESTAMP,?,?,NULL,?)',
                    [$newLeadId, $userId, 'Lead Created', 'Lead Created', 'Created']
                );
                
                $assignedTo = trim((string) ($validated['assignedTo'] ?? ''));
                if ($assignedTo !== '') {
                    $createdByUser = $userId !== '' ? $userId : $assignedTo;
                    DB::insert(
                        'INSERT INTO "LEAD_ACT" ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS")
                         VALUES (NEXT VALUE FOR GEN_LEAD_ACTID,?,?,CURRENT_TIMESTAMP,?,?,NULL,?)',
                        [$newLeadId, $createdByUser, 'Lead Assigned', 'Lead Assigned by '.$createdByUser.' to '.$assignedTo, 'Pending']
                    );
                }

                DB::update('UPDATE "LEAD" SET "LASTMODIFIED" = CURRENT_TIMESTAMP WHERE "LEADID" = ?', [$newLeadId]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput($request->only(array_keys($validated)))->with('error', 'Could not save the inquiry. Please try again.');
        }

        $assignedTo = trim((string) ($validated['assignedTo'] ?? ''));
        if ($assignedTo !== '' && $newLeadId > 0) {
            try {
                $this->sendInquiryAssignedEmail($assignedTo, $newLeadId);
            } catch (\Throwable $e) {
                \Log::error('Failed to send assignment email: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.inquiries')->with('success', 'Inquiry created.');
    }

    public function updateInquiry(Request $request, int $leadId): RedirectResponse
    {
        $country = trim((string) $request->input('COUNTRY', ''));
        $isMY = ($country === 'MY' || $country === 'Malaysia' || $country === '');

        $validated = $request->validate(
            [
                'COMPANYNAME' => 'required|string|max:50',
                'CONTACTNAME' => 'required|string|max:100',
                'CONTACTNO' => 'required|string|max:50',
                'EMAIL' => 'required|email|max:255',
                'ADDRESS1' => 'nullable|string|max:255',
                'ADDRESS2' => 'nullable|string|max:255',
                'CITY' => $isMY ? 'required|string|max:100' : 'nullable|string|max:100',
                'STATE' => 'nullable|string|max:100',
                'COUNTRY' => 'nullable|string|max:100',
                'POSTCODE' => $isMY ? 'required|string|digits:5' : 'nullable|string|max:20',
                'BUSINESSNATURE' => 'required|string|max:50',
                'USERCOUNT' => 'nullable|integer',
                'EXISTINGSOFTWARE' => 'required|string|max:40',
                'DEMOMODE' => 'required|string|in:Zoom,On-site',
                'product_interested' => 'required|array',
                'product_interested.*' => 'integer|in:1,2,3,4,5,6,7,8,9,10,11',
                'DESCRIPTION' => 'nullable|string|max:4000',
                'REFERRALCODE' => 'nullable|string|max:100',
                'INQUIRY_SNAPSHOT_AT' => 'nullable|string|max:50',
                'assignedTo' => 'nullable|string|max:50',
            ],
            [
                'POSTCODE.digits' => 'Invalid PostCode.',
                'product_interested.*' => 'Please select at least one product.',
                'product_interested.required' => 'Please select at least one product.',
            ],
            [
                'CONTACTNO' => 'Contact no',
                'POSTCODE' => 'Post code',
            ]
        );

        $exists = DB::selectOne('SELECT "LEADID", "ASSIGNEDTO" AS "oldAssignedTo" FROM "LEAD" WHERE "LEADID" = ?', [$leadId]);
        if (! $exists) {
            return redirect()->route('admin.inquiries')->with('error', 'Lead not found.');
        }
        $oldAssignedTo = trim((string) ($exists->oldAssignedTo ?? ''));

        $newAssignedTo = trim((string) ($validated['assignedTo'] ?? ''));
        if ($oldAssignedTo !== '' && $newAssignedTo !== $oldAssignedTo) {
            return back()->withInput($request->only(array_keys($validated)))->with('error', 'Reassignment or unassignment is not allowed once a dealer is assigned.');
        }

        $snapshotMessage = $this->inquiryEditSnapshotMessage($leadId, $request->input('INQUIRY_SNAPSHOT_AT'));
        if ($snapshotMessage !== null) {
            if ($snapshotMessage === 'Lead not found.') {
                return redirect()->route('admin.inquiries')->with('error', $snapshotMessage);
            }

            return redirect()->route('admin.inquiries.edit', $leadId)->with('error', $snapshotMessage);
        }

        // Allow updating assigned/in-progress inquiries without stale check

        // Same as create: if company name exists on another lead, show confirmation (exclude current lead)
        if (! $request->boolean('duplicate_ok')) {
            try {
                $existing = DB::selectOne(
                    'SELECT FIRST 1 l."LEADID",l."COMPANYNAME",l."CONTACTNAME",l."EMAIL",
                        COALESCE(
                            (SELECT FIRST 1 la."STATUS"
                             FROM "LEAD_ACT" la
                             WHERE la."LEADID" = l."LEADID"
                             ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                            \'Created\'
                        ) AS "CURRENTSTATUS",
                        l."CREATEDAT"
                     FROM "LEAD" l
                     WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
                       AND UPPER(TRIM(l."COMPANYNAME")) = UPPER(TRIM(?)) AND l."LEADID" <> ?
                     ORDER BY
                        CASE
                            WHEN UPPER(TRIM(COALESCE(
                                (SELECT FIRST 1 la2."STATUS"
                                 FROM "LEAD_ACT" la2
                                 WHERE la2."LEADID" = l."LEADID"
                                 ORDER BY la2."CREATIONDATE" DESC, la2."LEAD_ACTID" DESC),
                                \'\'
                            ))) = \'FAILED\' THEN 1
                            ELSE 0
                        END ASC,
                        COALESCE(l."LASTMODIFIED", l."CREATEDAT") DESC,
                        l."LEADID" DESC',
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
                            $parts[] = 'Lead #SQL-'.$otherLeadId;
                        }
                        if ($createdLabel) {
                            $parts[] = 'was created on '.$createdLabel;
                        }
                        if ($status !== '') {
                            $parts[] = 'with status '.$status;
                        }
                        $line2 = $parts ? implode(' ', $parts).'.' : '';
                        $message = trim($line1."\n\n".$line2);

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
                    "ADDRESS1" = ?, "ADDRESS2" = ?, "CITY" = ?, "STATE" = ?, "COUNTRY" = ?, "POSTCODE" = ?, "BUSINESSNATURE" = ?,
                    "USERCOUNT" = ?, "EXISTINGSOFTWARE" = ?, "DEMOMODE" = ?, "DESCRIPTION" = ?, "REFERRALCODE" = ?, "ASSIGNEDTO" = ?,
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
                    $validated['CITY'] ?? null,
                    $validated['STATE'] ?? null,
                    $validated['COUNTRY'] ?? null,
                    $validated['POSTCODE'] ?? null,
                    $validated['BUSINESSNATURE'],
                    $validated['USERCOUNT'] ?? null,
                    $validated['EXISTINGSOFTWARE'],
                    $validated['DEMOMODE'],
                    $descriptionValue,
                    $validated['REFERRALCODE'] ?? null,
                    $validated['assignedTo'] ?? null,
                    $leadId,
                ]
            );
        } catch (\Throwable $e) {
            return back()->withInput($request->only(array_keys($validated)))->with('error', 'Could not update the inquiry. Please try again.');
        }

        // Insert "Lead Assigned" / "Lead Unassigned" activity if ASSIGNEDTO changed
        $newAssignedTo = trim((string) ($validated['assignedTo'] ?? ''));
        if ($newAssignedTo !== $oldAssignedTo) {
            $assignUserId = trim((string) ($request->session()->get('user_id') ?? ''));
            
            if ($newAssignedTo !== '') {
                $createdByUser = $assignUserId !== '' ? $assignUserId : $newAssignedTo;
                try {
                    DB::insert(
                        'INSERT INTO "LEAD_ACT" ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS")
                         VALUES (NEXT VALUE FOR GEN_LEAD_ACTID,?,?,CURRENT_TIMESTAMP,?,?,NULL,?)',
                        [$leadId, $createdByUser, 'Lead Assigned', 'Lead Assigned by '.$createdByUser.' to '.$newAssignedTo, 'Pending']
                    );
                    DB::update('UPDATE "LEAD" SET "LASTMODIFIED" = CURRENT_TIMESTAMP WHERE "LEADID" = ?', [$leadId]);
                } catch (\Throwable $e) {
                    \Log::error('Failed to insert Lead Assigned activity: '.$e->getMessage());
                }
            } else {
                $createdByUser = $assignUserId !== '' ? $assignUserId : $oldAssignedTo;
                try {
                    DB::insert(
                        'INSERT INTO "LEAD_ACT" ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS")
                         VALUES (NEXT VALUE FOR GEN_LEAD_ACTID,?,?,CURRENT_TIMESTAMP,?,?,NULL,?)',
                        [$leadId, $createdByUser, 'Lead Unassigned', 'Lead Unassigned by '.$createdByUser, 'Created']
                    );
                    DB::update('UPDATE "LEAD" SET "LASTMODIFIED" = CURRENT_TIMESTAMP WHERE "LEADID" = ?', [$leadId]);
                } catch (\Throwable $e) {
                    \Log::error('Failed to insert Lead Unassigned activity: '.$e->getMessage());
                }
            }
        }

        $activeTab = $request->input('return_tab');
        if (! $activeTab) {
            $leadAfterUpdate = DB::selectOne('SELECT "ASSIGNEDTO" AS "assignedTo" FROM "LEAD" WHERE "LEADID" = ?', [$leadId]);
            $activeTab = (! empty($leadAfterUpdate->assignedTo)) ? 'assigned' : 'incoming';
        }

        return redirect()->route('admin.inquiries', ['tab' => $activeTab])->with('success', 'Inquiry updated.');
    }

    public function deleteInquiry(Request $request, int $leadId): \Illuminate\Http\JsonResponse
    {
        $row = DB::selectOne(
            'SELECT "LEADID" FROM "LEAD" WHERE "LEADID" = ? AND COALESCE("ISDELETED", FALSE) = FALSE',
            [$leadId]
        );
        if (! $row) {
            return response()->json(['success' => false, 'message' => 'Lead not found.'], 404);
        }

        $staleMessage = $this->incomingInquiryStaleMessage($leadId);
        if ($staleMessage !== null) {
            $code = $staleMessage === 'Lead not found.' ? 404 : 409;

            return response()->json(['success' => false, 'message' => $staleMessage], $code);
        }

        try {
            DB::update('UPDATE "LEAD" SET "ISDELETED" = TRUE, "LASTMODIFIED" = CURRENT_TIMESTAMP WHERE "LEADID" = ?', [$leadId]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Could not delete inquiry.'], 500);
        }

        // Store lead ID for undo (soft delete — just flip ISDELETED back)
        $request->session()->put('delete_undo', [
            'lead_id' => $leadId,
        ]);

        return response()->json(['success' => true]);
    }

    public function batchDeleteInquiries(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'required|integer|min:1',
        ]);

        $leadIds = array_map('intval', $validated['lead_ids']);
        if (empty($leadIds)) {
            return response()->json(['success' => false, 'message' => 'No inquiry IDs provided.'], 400);
        }

        try {
            DB::beginTransaction();
            foreach ($leadIds as $id) {
                DB::update('UPDATE "LEAD" SET "ISDELETED" = TRUE, "LASTMODIFIED" = CURRENT_TIMESTAMP WHERE "LEADID" = ?', [$id]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Could not delete inquiries: '.$e->getMessage()], 500);
        }

        return response()->json(['success' => true]);
    }

    public function updateInquiryField(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => 'required|integer|min:1',
            'field' => 'required|string',
            'value' => 'nullable|string',
        ]);

        $leadId = (int) $validated['lead_id'];
        $field = strtoupper(trim($validated['field']));
        $value = $validated['value'];

        $allowedFields = [
            'CONTACTNAME', 'POSTCODE', 'CITY', 'STATE',
            'BUSINESSNATURE', 'EXISTINGSOFTWARE',
            'DEMOMODE', 'PRODUCTID', 'USERCOUNT', 'REFERRALCODE', 'DESCRIPTION',
        ];

        if (! in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Invalid column name.'], 400);
        }

        if ($field === 'USERCOUNT') {
            $value = ($value !== null && $value !== '') ? (int) $value : null;
        }

        try {
            DB::beginTransaction();
            $affected = DB::update(
                'UPDATE "LEAD" SET "'.$field.'" = ?, "LASTMODIFIED" = CURRENT_TIMESTAMP WHERE "LEADID" = ?',
                [$value, $leadId]
            );
            DB::commit();
            \Log::info("updateInquiryField: leadId={$leadId}, field={$field}, value={$value}, affected_rows={$affected}");

            return response()->json(['success' => true, 'affected' => $affected]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('updateInquiryField failed: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Database error: '.$e->getMessage()], 500);
        }
    }

    public function undoDeleteInquiry(Request $request): RedirectResponse
    {
        $validated = $request->validate(['LEADID' => 'required|integer|min:1']);
        $leadId = (int) $validated['LEADID'];

        $data = $request->session()->get('delete_undo');
        if (! $data || (int) ($data['lead_id'] ?? 0) !== $leadId) {
            return redirect()->route('admin.inquiries')->with('error', 'Cannot undo: delete session expired or invalid.');
        }

        try {
            DB::update(
                'UPDATE "LEAD" SET "ISDELETED" = FALSE, "LASTMODIFIED" = CURRENT_TIMESTAMP WHERE "LEADID" = ?',
                [$leadId]
            );
        } catch (\Throwable $e) {
            return redirect()->route('admin.inquiries')->with('error', 'Could not undo the delete. Please try again.');
        }

        $request->session()->forget('delete_undo');

        return redirect()->route('admin.inquiries')->with('success', 'Delete undone. Lead #SQL-'.$leadId.' restored.');
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
        if (! is_string($path) || $path === '') {
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

        if ($htmlResponse = \App\Support\AttachmentUrlBuilder::serveHtmlWrapper((string) $request->query('name', ''), $mime, $request)) {
            return $htmlResponse;
        }

        $headers = ['Content-Type' => $mime];
        $name = (string) ($request->query('name') ?: urldecode($request->route('filename') ?? ''));
        if ($name !== '') {
            $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
            if ($ext && !str_ends_with(strtolower($name), '.' . strtolower($ext))) {
                $name .= '.' . $ext;
            }
            $headers['Content-Disposition'] = 'inline; filename="' . addslashes($name) . '"';
        }

        return response()->file($fullPath, $headers);
    }

    /**
     * Serve a single activity attachment (image) for reward rows (admin).
     * Supports path-based storage or binary BLOB in DB.
     */
    public function rewardActivityAttachment(Request $request, int $leadId, int $leadActId): \Symfony\Component\HttpFoundation\Response
    {
        $row = DB::selectOne('SELECT "ATTACHMENT" FROM "LEAD_ACT" WHERE "LEAD_ACTID" = ? AND "LEADID" = ?', [$leadActId, $leadId]);
        if (! $row) {
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

            if ($htmlResponse = \App\Support\AttachmentUrlBuilder::serveHtmlWrapper((string) $request->query('name', ''), $mime, $request)) {
                return $htmlResponse;
            }

            $headers = ['Content-Type' => $mime];
            $name = $request->query('name');
            if (is_string($name) && $name !== '') {
                $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                if ($ext && !str_ends_with(strtolower($name), '.' . strtolower($ext))) {
                    $name .= '.' . $ext;
                }
                $headers['Content-Disposition'] = 'inline; filename="' . addslashes($name) . '"';
            }

            return response()->file($fullPath, $headers);
        }
        if (is_string($attachment) && strlen($attachment) > 0) {
            $mime = 'image/jpeg';
            $ext = 'jpg';
            if (preg_match('/^\x89PNG/', $attachment)) {
                $mime = 'image/png';
                $ext = 'png';
            } elseif (str_starts_with($attachment, "\xFF\xD8")) {
                $mime = 'image/jpeg';
                $ext = 'jpg';
            } elseif (str_starts_with($attachment, 'GIF8')) {
                $mime = 'image/gif';
                $ext = 'gif';
            } elseif (str_starts_with($attachment, 'RIFF') && substr($attachment, 8, 4) === 'WEBP') {
                $mime = 'image/webp';
                $ext = 'webp';
            }

            if ($htmlResponse = \App\Support\AttachmentUrlBuilder::serveHtmlWrapper((string) $request->query('name', ''), $mime, $request)) {
                return $htmlResponse;
            }

            $headers = ['Content-Type' => $mime];
            $name = $request->query('name');
            if (is_string($name) && $name !== '') {
                if (!str_ends_with(strtolower($name), '.' . $ext)) {
                    $name .= '.' . $ext;
                }
                $headers['Content-Disposition'] = 'inline; filename="' . addslashes($name) . '"';
            }

            return response($attachment, 200, $headers);
        }

        return response('', 404);
    }

    /**
     * Send email to the dealer (assigned user) for a completed payout (uses SMTP).
     * Dealer email is taken from USERS table by assignedTo.
     */
    public function sendPayoutEmail(Request $request): JsonResponse
    {
        $request->validate(['lead_id' => 'required|integer|min:1']);

        $leadId = (int) $request->input('lead_id');
        $lead = DB::selectOne(
            'SELECT "LEADID","COMPANYNAME","CONTACTNAME","ASSIGNEDTO" AS "assignedTo","REFERRALCODE" FROM "LEAD" WHERE "LEADID" = ?',
            [$leadId]
        );
        if (! $lead) {
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
                'message' => 'This inquiry is already '.$displayStatus.'. Please sync and try again.',
            ], 409);
        }

        $referralCode = trim((string) ($lead->REFERRALCODE ?? ''));
        if ($referralCode === '' || strtolower($referralCode) === 'noreferral') {
            return response()->json(['success' => false, 'message' => 'Referral code is required before sending this email.'], 400);
        }

        $assignedTo = trim((string) ($lead->assignedTo ?? ''));
        if ($assignedTo === '') {
            return response()->json(['success' => false, 'message' => 'No dealer assigned to this lead.'], 400);
        }

        $user = DB::selectOne(
            'SELECT "USERID","EMAIL","ALIAS","COMPANY" FROM "USERS" WHERE CAST("USERID" AS VARCHAR(50)) = ?',
            [$assignedTo]
        );
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Assigned dealer not found in users.'], 400);
        }

        $email = trim((string) ($user->EMAIL ?? ''));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
                inquiryId: 'SQL-'.(string) ($lead->LEADID ?? $leadId),
                referralCode: $referralCode,
                senderAlias: $senderAlias !== '' ? $senderAlias : 'SQL LMS',
                companyName: trim((string) ($lead->COMPANYNAME ?? ''))
            ));

            return response()->json(['success' => true, 'message' => 'Email sent to dealer: '.$email.'.']);
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
            return $company.' - '.$alias;
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
             WHERE CAST("USERID" AS VARCHAR(50)) IN ('.$placeholders.')',
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
                    'label' => 'All dealers',
                    'search' => 'all dealers no estream no e stream',
                ],
                'estream' => [
                    'label' => 'All eStream',
                    'search' => 'all estream all e stream',
                ],
            ];

            try {
                $dealerRows = DB::select(
                    'SELECT u."USERID", u."COMPANY", u."ALIAS", u."EMAIL"
                     FROM "USERS" u
                     WHERE TRIM(u."SYSTEMROLE") = ?
                     ORDER BY UPPER(TRIM(COALESCE(u."COMPANY", \'\'))),
                              UPPER(TRIM(COALESCE(u."ALIAS", \'\'))),
                              UPPER(TRIM(COALESCE(u."EMAIL", \'\'))),
                              u."USERID"',
                    [\App\Support\AppConstants::ROLE_DEALER]
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
                    $label = $company.' ('.$alias.')';
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

                $options['dealer:'.$dealerId] = [
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

    private function adminReportAreaOptions(): array
    {
        return QueryCache::remember('admin_report_area_options', function () {
            $path = base_path('malaysia-postcodes.json');
            if (! is_file($path)) {
                return [];
            }

            try {
                $data = json_decode((string) file_get_contents($path), true);
                $allCities = [];
                if (isset($data['state']) && is_array($data['state'])) {
                    foreach ($data['state'] as $state) {
                        if (isset($state['city']) && is_array($state['city'])) {
                            foreach ($state['city'] as $city) {
                                $cityName = strtoupper(trim((string) ($city['name'] ?? '')));
                                if ($cityName !== '') {
                                    $allCities[] = $cityName;
                                }
                            }
                        }
                    }
                }
                $cities = array_values(array_unique($allCities));
                sort($cities);

                return $cities;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    private function resolveAdminReportArea(Request $request): string
    {
        $area = strtoupper(trim((string) $request->query('report_area', '')));
        if ($area === 'ALL' || $area === '') {
            return '';
        }

        return $area;
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
                    ' AND ('.$ownerColumnSql.' IS NULL OR UPPER(TRIM(COALESCE('.$companyColumnSql.', \'\'))) <> ?)',
                    [$estreamCompany],
                ];
            }

            return [
                ' AND UPPER(TRIM(COALESCE('.$companyColumnSql.', \'\'))) <> ?',
                [$estreamCompany],
            ];
        }

        if ($selectedScope === 'estream') {
            return [
                ' AND UPPER(TRIM(COALESCE('.$companyColumnSql.', \'\'))) = ?',
                [$estreamCompany],
            ];
        }

        if (str_starts_with($selectedScope, 'dealer:')) {
            $dealerId = trim(substr($selectedScope, 7));

            return $dealerId === ''
                ? [' AND 1 = 0', []]
                : [' AND TRIM(CAST('.$ownerColumnSql.' AS VARCHAR(50))) = ?', [$dealerId]];
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
                    WHERE ux."USERID" = '.$ownerColumnSql.'
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
                    WHERE ux."USERID" = '.$ownerColumnSql.'
                      AND UPPER(TRIM(COALESCE(ux."COMPANY", \'\'))) = ?
                )',
                [$estreamCompany],
            ];
        }

        if (str_starts_with($selectedScope, 'dealer:')) {
            $dealerId = trim(substr($selectedScope, 7));

            return $dealerId === ''
                ? [' AND 1 = 0', []]
                : [' AND TRIM(CAST('.$ownerColumnSql.' AS VARCHAR(50))) = ?', [$dealerId]];
        }

        return [' AND 1 = 0', []];
    }

    public function reports(Request $request): View
    {
        $selectedReportScope = $this->resolveAdminReportScope($request);
        $reportScopeOptions = $this->adminReportScopeOptions();
        $selectedArea = $this->resolveAdminReportArea($request);
        $areaOptions = $this->adminReportAreaOptions();

        $daysParam = $request->query('days', '60');
        $fromParam = trim((string) $request->query('from', ''));
        $toParam = trim((string) $request->query('to', ''));
        $useCustom = $fromParam !== '' && $toParam !== '';

        if ($useCustom) {
            try {
                $startDate = Carbon::parse($fromParam)->startOfDay();
                $endDate = Carbon::parse($toParam)->endOfDay();
                if ($startDate->gt($endDate)) {
                    $useCustom = false;
                } else {
                    $days = (int) max(1, $startDate->diffInDays($endDate) + 1);
                }
            } catch (\Throwable $e) {
                $useCustom = false;
            }
        }

        if (! $useCustom) {
            $days = (int) $daysParam;
            if (! in_array($days, [30, 60, 90], true)) {
                $days = 60;
            }
            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        $periodLabel = $startDate->format('d/m/Y').' - '.$endDate->format('d/m/Y');
        $startStr = $startDate->format('Y-m-d H:i:s');
        $endStr = $endDate->format('Y-m-d H:i:s');

        $currentRangeDays = (int) ($startDate->diffInDays($endDate) + 1);
        $prevStartDate = $startDate->copy()->subDays($currentRangeDays)->startOfDay();
        $prevEndDate = $startDate->copy()->subSecond();
        $prevStartStr = $prevStartDate->format('Y-m-d H:i:s');
        $prevEndStr = $prevEndDate->format('Y-m-d H:i:s');

        $unassignedCount = 0;
        if ($selectedReportScope === 'all' && ! $selectedArea) {
            $unassignedRow = DB::selectOne(
                'SELECT COUNT(*) as c FROM "LEAD" WHERE COALESCE("ISDELETED", FALSE) = FALSE AND ("ASSIGNEDTO" IS NULL OR TRIM(CAST("ASSIGNEDTO" AS VARCHAR(50))) = \'\') AND "CREATEDAT" >= ? AND "CREATEDAT" <= ?',
                [$startStr, $endStr]
            );
            $unassignedCount = (int) ($unassignedRow->C ?? $unassignedRow->c ?? 0);
        }

        [$leadScopeSql, $leadScopeBindings] = $this->buildAdminReportScopeSql(
            $selectedReportScope,
            'l."ASSIGNEDTO"',
            'u."COMPANY"',
            true
        );

        if ($selectedArea) {
            $leadScopeSql .= ' AND UPPER(TRIM(u."CITY")) = ?';
            $leadScopeBindings[] = $selectedArea;
        }

        [$payoutScopeSql, $payoutScopeBindings] = $this->buildAdminReportScopeSql(
            $selectedReportScope,
            'p."USERID"',
            'u."COMPANY"'
        );

        if ($selectedArea && $selectedArea !== 'ALL') {
            $payoutScopeSql .= ' AND UPPER(TRIM(u."CITY")) = ?';
            $payoutScopeBindings[] = $selectedArea;
        }

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

        // Lead status summary (derived from LEAD_ACT)
        $leadStatusRows = DB::select(
            "SELECT
                CASE
                    WHEN ls.latest_status IN ('PENDING', 'FOLLOWUP', 'FOLLOW UP', 'DEMO', 'CONFIRMED') THEN 'Ongoing'
                    WHEN ls.latest_status IN ('COMPLETED', 'REWARDED', 'PAID', 'REWARD DISTRIBUTED') THEN 'Closed'
                    WHEN ls.latest_status = 'FAILED' THEN 'Failed'
                    WHEN ls.latest_status = 'CANCELLED' THEN 'Cancelled'
                    ELSE 'Open'
                END AS status,
                COUNT(*) AS c
             FROM \"LEAD\" l
             LEFT JOIN \"USERS\" u ON u.\"USERID\" = l.\"ASSIGNEDTO\"
             LEFT JOIN (
                 SELECT a.\"LEADID\", UPPER(TRIM(a.\"STATUS\" )) AS latest_status
                 FROM \"LEAD_ACT\" a
                 JOIN (
                     SELECT \"LEADID\", MAX(\"CREATIONDATE\") AS MAXCD
                     FROM \"LEAD_ACT\"
                     GROUP BY \"LEADID\"
                 ) m ON m.\"LEADID\" = a.\"LEADID\" AND m.MAXCD = a.\"CREATIONDATE\"
             ) ls ON ls.\"LEADID\" = l.\"LEADID\"
             WHERE COALESCE(l.\"ISDELETED\", FALSE) = FALSE AND l.\"CREATEDAT\" >= ? AND l.\"CREATEDAT\" <= ?
             ".$leadScopeSql."
             GROUP BY
                CASE
                    WHEN ls.latest_status IN ('PENDING', 'FOLLOWUP', 'FOLLOW UP', 'DEMO', 'CONFIRMED') THEN 'Ongoing'
                    WHEN ls.latest_status IN ('COMPLETED', 'REWARDED', 'PAID', 'REWARD DISTRIBUTED') THEN 'Closed'
                    WHEN ls.latest_status = 'FAILED' THEN 'Failed'
                    WHEN ls.latest_status = 'CANCELLED' THEN 'Cancelled'
                    ELSE 'Open'
                END",
            array_merge([$startStr, $endStr], $leadScopeBindings)
        );
        $leadStatus = [
            'Open' => 0,
            'Ongoing' => 0,
            'Closed' => 0,
            'Failed' => 0,
            'Cancelled' => 0,
        ];
        foreach ($leadStatusRows as $row) {
            $key = (string) $get($row, 'status');
            if (isset($leadStatus[$key])) {
                $leadStatus[$key] = (int) $get($row, 'c');
            }
        }

        // Last month lead status summary for month-over-month status comparisons.
        $lastMonthLeadStatusRows = DB::select(
            "SELECT
                CASE
                    WHEN ls.latest_status IN ('PENDING', 'FOLLOWUP', 'FOLLOW UP', 'DEMO', 'CONFIRMED') THEN 'Ongoing'
                    WHEN ls.latest_status IN ('COMPLETED', 'REWARDED', 'PAID', 'REWARD DISTRIBUTED') THEN 'Closed'
                    WHEN ls.latest_status = 'FAILED' THEN 'Failed'
                    WHEN ls.latest_status = 'CANCELLED' THEN 'Cancelled'
                    ELSE 'Open'
                END AS status,
                COUNT(*) AS c
             FROM \"LEAD\" l
             LEFT JOIN \"USERS\" u ON u.\"USERID\" = l.\"ASSIGNEDTO\"
             LEFT JOIN (
                 SELECT a.\"LEADID\", UPPER(TRIM(a.\"STATUS\")) AS latest_status
                 FROM \"LEAD_ACT\" a
                 JOIN (
                     SELECT \"LEADID\", MAX(\"CREATIONDATE\") AS MAXCD
                     FROM \"LEAD_ACT\"
                     GROUP BY \"LEADID\"
                 ) m ON m.\"LEADID\" = a.\"LEADID\" AND m.MAXCD = a.\"CREATIONDATE\"
             ) ls ON ls.\"LEADID\" = l.\"LEADID\"
             WHERE COALESCE(l.\"ISDELETED\", FALSE) = FALSE AND l.\"CREATEDAT\" >= ? AND l.\"CREATEDAT\" <= ?
             ".$leadScopeSql."
             GROUP BY
                CASE
                    WHEN ls.latest_status IN ('PENDING', 'FOLLOWUP', 'FOLLOW UP', 'DEMO', 'CONFIRMED') THEN 'Ongoing'
                    WHEN ls.latest_status IN ('COMPLETED', 'REWARDED', 'PAID', 'REWARD DISTRIBUTED') THEN 'Closed'
                    WHEN ls.latest_status = 'FAILED' THEN 'Failed'
                    WHEN ls.latest_status = 'CANCELLED' THEN 'Cancelled'
                    ELSE 'Open'
                END",
            array_merge([$prevStartStr, $prevEndStr], $leadScopeBindings)
        );
        $lastMonthLeadStatus = [
            'Open' => 0,
            'Ongoing' => 0,
            'Closed' => 0,
            'Failed' => 0,
            'Cancelled' => 0,
        ];
        foreach ($lastMonthLeadStatusRows as $row) {
            $key = (string) $get($row, 'status');
            if (isset($lastMonthLeadStatus[$key])) {
                $lastMonthLeadStatus[$key] = (int) $get($row, 'c');
            }
        }

        // Unassigned leads: match LEAD status "Open"
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
                'CANCELLED' => 'Cancelled',
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
                 WHERE "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?
                 GROUP BY "LEADID"
             ) m ON m."LEADID" = a."LEADID" AND m.max_created = a."CREATIONDATE"
             JOIN "LEAD" l ON l."LEADID" = a."LEADID"
             LEFT JOIN "USERS" u ON u."USERID" = l."ASSIGNEDTO"
             WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
             '.$leadScopeSql.'
             GROUP BY a."STATUS"',
            array_merge([$startStr, $endStr], $leadScopeBindings)
        );
        $activityStatus = [
            'Created' => 0,
            'Pending' => 0,
            'FollowUp' => 0,
            'Demo' => 0,
            'Confirmed' => 0,
            'Completed' => 0,
            'Failed' => 0,
            'Cancelled' => 0,
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
                 WHERE "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?
                 GROUP BY "LEADID"
             ) m ON m."LEADID" = a."LEADID" AND m.max_created = a."CREATIONDATE"
             JOIN "LEAD" l ON l."LEADID" = a."LEADID"
             LEFT JOIN "USERS" u ON u."USERID" = l."ASSIGNEDTO"
             WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
             '.$leadScopeSql.'
             GROUP BY a."STATUS"',
            array_merge([$prevStartStr, $prevEndStr], $leadScopeBindings)
        );
        $lastMonthActivity = [
            'Created' => 0,
            'Pending' => 0,
            'FollowUp' => 0,
            'Demo' => 0,
            'Confirmed' => 0,
            'Completed' => 0,
            'Failed' => 0,
            'Cancelled' => 0,
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
             WHERE p."DATEGENERATED" >= ? AND p."DATEGENERATED" <= ?
             '.$payoutScopeSql.'
             GROUP BY p."STATUS"',
            array_merge([$startStr, $endStr], $payoutScopeBindings)
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
             WHERE p."DATEGENERATED" >= ? AND p."DATEGENERATED" <= ?
             '.$payoutScopeSql.'
             GROUP BY p."STATUS"',
            array_merge([$prevStartStr, $prevEndStr], $payoutScopeBindings)
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

        // Inquiry trend for period (leads created)
        // Inquiry trend for current month (leads created)
        $trendRows = DB::select(
            'SELECT CAST(l."CREATEDAT" AS DATE) AS d, COUNT(*) AS c
             FROM "LEAD" l
             LEFT JOIN "USERS" u ON u."USERID" = l."ASSIGNEDTO"
             WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."CREATEDAT" >= ? AND l."CREATEDAT" <= ?
             '.$leadScopeSql.'
             GROUP BY CAST(l."CREATEDAT" AS DATE)
             ORDER BY CAST(l."CREATEDAT" AS DATE)',
            array_merge([$startStr, $endStr], $leadScopeBindings)
        );
        $trendByDay = [];
        for ($i = 0; $i < $currentRangeDays; $i++) {
            $dateKey = $startDate->copy()->addDays($i)->format('Y-m-d');
            $trendByDay[$dateKey] = 0;
        }
        foreach ($trendRows as $row) {
            $d = $get($row, 'd');
            if ($d) {
                $dateKey = Carbon::parse($d)->format('Y-m-d');
                if (isset($trendByDay[$dateKey])) {
                    $trendByDay[$dateKey] += (int) $get($row, 'c');
                }
            }
        }
        $inquiryTrend = [];
        foreach ($trendByDay as $dayKey => $count) {
            $inquiryTrend[] = [
                'day' => Carbon::parse($dayKey)->format('M j'),
                'full_day' => Carbon::parse($dayKey)->format('d/m/Y'),
                'count' => $count,
            ];
        }

        $currentMonthTotal = array_sum($trendByDay);
        $lastMonthRows = DB::select(
            'SELECT COUNT(*) AS c
             FROM "LEAD" l
             LEFT JOIN "USERS" u ON u."USERID" = l."ASSIGNEDTO"
             WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."CREATEDAT" >= ? AND l."CREATEDAT" <= ?
             '.$leadScopeSql,
            array_merge([$prevStartStr, $prevEndStr], $leadScopeBindings)
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
            'Cancelled' => $percentChange($activityStatus['Cancelled'] ?? 0, $lastMonthActivity['Cancelled'] ?? 0),
            'Rewarded' => $percentChange($activityStatus['reward'] ?? 0, $lastMonthActivity['reward'] ?? 0),
        ];

        // Product Conversion Rate (from LEAD_ACT.DEALTPRODUCT) for current month
        $productIds = ProductConstants::ids();
        $productNames = ProductConstants::fullNames();
        $productCounts = array_fill_keys($productIds, 0);
        $dealRows = DB::select(
            'SELECT a."DEALTPRODUCT" AS dealt
             FROM "LEAD_ACT" a
             JOIN "LEAD" l ON l."LEADID" = a."LEADID"
             LEFT JOIN "USERS" u ON u."USERID" = l."ASSIGNEDTO"
             WHERE COALESCE(l."ISDELETED", FALSE) = FALSE
               AND a."DEALTPRODUCT" IS NOT NULL
               AND TRIM(a."DEALTPRODUCT") <> \'\'
               AND a."CREATIONDATE" >= ?
               AND a."CREATIONDATE" <= ?
               '.$leadScopeSql,
            array_merge([$startStr, $endStr], $leadScopeBindings)
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
                'label' => $productNames[$pid] ?? ('Product '.$pid),
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
            'days' => $days,
            'periodLabel' => $periodLabel,
            'selectedReportScope' => $selectedReportScope,
            'reportScopeOptions' => $reportScopeOptions,
            'to' => $useCustom ? $toParam : null,
            'selectedArea' => $selectedArea,
            'areaOptions' => $areaOptions,
        ]);
    }

    public function reportsV2(Request $request): View
    {
        $daysParam = $request->query('days', '60');
        $compareDaysParam = $request->query('compare_days', '30');
        $primaryFrom = trim((string) $request->query('primary_from', ''));
        $primaryTo = trim((string) $request->query('primary_to', ''));
        $compareFrom = trim((string) $request->query('compare_from', ''));
        $compareTo = trim((string) $request->query('compare_to', ''));
        $selectedReportScope = $this->resolveAdminReportScope($request);
        $reportScopeOptions = $this->adminReportScopeOptions();
        $selectedArea = $this->resolveAdminReportArea($request);
        $areaOptions = $this->adminReportAreaOptions();

        [$dealerScopeSql, $dealerScopeBindings] = $this->buildAdminReportExistsScopeSql(
            $selectedReportScope,
            'l."ASSIGNEDTO"'
        );

        if ($selectedArea) {
            $dealerScopeSql .= ' AND EXISTS (SELECT 1 FROM "USERS" uarea WHERE uarea."USERID" = l."ASSIGNEDTO" AND UPPER(TRIM(uarea."CITY")) = ?)';
            $dealerScopeBindings[] = $selectedArea;
        }

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
        if (! $useCustomPrimary) {
            $days = (int) $daysParam;
            if (! in_array($days, [30, 60, 90], true)) {
                $days = 60;
            }
        }

        $compareDays = (int) $compareDaysParam;
        if (! in_array($compareDays, [30, 60, 90], true)) {
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
        if (! $useCustomCompare) {
            $compareStartStr = null;
            $compareEndStr = null;
        }

        // Build primary period filter: either DATEADD (preset) or timestamp bounds (custom)
        if ($useCustomPrimary) {
            $dealerTotals = DB::select(
                'SELECT l."ASSIGNEDTO" AS dealer_id,
                        COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL") AS name,
                        COUNT(*) AS total_c,
                        SUM(CASE WHEN (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                                       FROM "LEAD_ACT" la
                                       WHERE la."LEADID" = l."LEADID"
                                       ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                                      ) IN (\'COMPLETED\', \'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\') THEN 1 ELSE 0 END) AS closed_c
                 FROM "LEAD" l
                 JOIN "USERS" u ON u."USERID" = l."ASSIGNEDTO"
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   AND l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP)
                   AND COALESCE((SELECT FIRST 1 UPPER(TRIM(la2."STATUS")) FROM "LEAD_ACT" la2 WHERE la2."LEADID" = l."LEADID" ORDER BY la2."CREATIONDATE" DESC, la2."LEAD_ACTID" DESC), \'\') <> \'CANCELLED\'
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO", COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL")',
                array_merge([$primaryStartStr, $primaryEndStr], $dealerScopeBindings)
            );
        } else {
            $dealerTotals = DB::select(
                'SELECT l."ASSIGNEDTO" AS dealer_id,
                        COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL") AS name,
                        COUNT(*) AS total_c,
                        SUM(CASE WHEN (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                                       FROM "LEAD_ACT" la
                                       WHERE la."LEADID" = l."LEADID"
                                       ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                                      ) IN (\'COMPLETED\', \'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\') THEN 1 ELSE 0 END) AS closed_c
                 FROM "LEAD" l
                 JOIN "USERS" u ON u."USERID" = l."ASSIGNEDTO"
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   AND l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE)
                   AND COALESCE((SELECT FIRST 1 UPPER(TRIM(la2."STATUS")) FROM "LEAD_ACT" la2 WHERE la2."LEADID" = l."LEADID" ORDER BY la2."CREATIONDATE" DESC, la2."LEAD_ACTID" DESC), \'\') <> \'CANCELLED\'
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO", COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL")',
                array_merge([-$days], $dealerScopeBindings)
            );
        }

        $dealerNameById = $this->adminReportDealerDisplayNames(array_map(
            static fn ($row) => (string) ($row->DEALER_ID ?? $row->dealer_id ?? ''),
            $dealerTotals
        ));

        $totalsByDealer = [];
        foreach ($dealerTotals as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '') {
                continue;
            }
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
                'SELECT l."ASSIGNEDTO" AS dealer_id, COUNT(*) AS c
                 FROM "LEAD" l
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   AND l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP)
                   AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                        FROM "LEAD_ACT" la
                        WHERE la."LEADID" = l."LEADID"
                        ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                       ) IN (\'COMPLETED\', \'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\')
                   AND NOT EXISTS (SELECT 1 FROM "LEAD_ACT" a WHERE a."LEADID" = l."LEADID" AND UPPER(TRIM(a."STATUS")) = \'COMPLETED\')
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO"',
                array_merge([$primaryStartStr, $primaryEndStr], $dealerScopeBindings)
            );
        } else {
            $rejectedRows = DB::select(
                'SELECT l."ASSIGNEDTO" AS dealer_id, COUNT(*) AS c
                 FROM "LEAD" l
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   AND l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE)
                   AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                        FROM "LEAD_ACT" la
                        WHERE la."LEADID" = l."LEADID"
                        ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                       ) IN (\'COMPLETED\', \'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\')
                   AND NOT EXISTS (SELECT 1 FROM "LEAD_ACT" a WHERE a."LEADID" = l."LEADID" AND UPPER(TRIM(a."STATUS")) = \'COMPLETED\')
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO"',
                array_merge([-$days], $dealerScopeBindings)
            );
        }
        foreach ($rejectedRows as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '' || ! isset($totalsByDealer[$id])) {
                continue;
            }
            $rej = (int) ($r->C ?? $r->c ?? 0);
            $totalsByDealer[$id]['rejected'] = $rej;
            $total = (int) $totalsByDealer[$id]['total'];
            $totalsByDealer[$id]['rejection_rate'] = $total > 0 ? ($rej / $total * 100) : 0;
        }

        // Failed count in primary period
        if ($useCustomPrimary) {
            $failedCountRows = DB::select(
                'SELECT l."ASSIGNEDTO" AS dealer_id, COUNT(*) AS failed_c
                 FROM "LEAD" l
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   AND l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP)
                   AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                        FROM "LEAD_ACT" la
                        WHERE la."LEADID" = l."LEADID"
                        ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                       ) = \'FAILED\'
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO"',
                array_merge([$primaryStartStr, $primaryEndStr], $dealerScopeBindings)
            );
        } else {
            $failedCountRows = DB::select(
                'SELECT l."ASSIGNEDTO" AS dealer_id, COUNT(*) AS failed_c
                FROM "LEAD" l
                WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   AND l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE)
                   AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                        FROM "LEAD_ACT" la
                        WHERE la."LEADID" = l."LEADID"
                        ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                       ) = \'FAILED\'
                   '.$dealerScopeSql.'
                GROUP BY l."ASSIGNEDTO"',
                array_merge([-$days], $dealerScopeBindings)
            );
        }
        foreach ($failedCountRows as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '' || ! isset($totalsByDealer[$id])) {
                continue;
            }
            $totalsByDealer[$id]['failed'] = (int) ($r->FAILED_C ?? $r->failed_c ?? 0);
            $total = (int) $totalsByDealer[$id]['total'];
            $totalsByDealer[$id]['fail_rate'] = $total > 0 ? round($totalsByDealer[$id]['failed'] / $total * 100, 1) : 0;
        }
        foreach ($totalsByDealer as $id => $d) {
            if (! isset($d['failed'])) {
                $totalsByDealer[$id]['failed'] = 0;
                $totalsByDealer[$id]['fail_rate'] = 0.0;
            }
        }

        // Comparison period: total and failed per dealer for increase fail rate
        if ($useCustomCompare) {
            $compareTotals = DB::select(
                'SELECT l."ASSIGNEDTO" AS dealer_id,
                        COUNT(*) AS total_c,
                        SUM(CASE WHEN (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                                       FROM "LEAD_ACT" la
                                       WHERE la."LEADID" = l."LEADID"
                                       ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                                      ) = \'FAILED\' THEN 1 ELSE 0 END) AS failed_c
                 FROM "LEAD" l
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   AND l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP)
                   AND COALESCE((SELECT FIRST 1 UPPER(TRIM(la2."STATUS")) FROM "LEAD_ACT" la2 WHERE la2."LEADID" = l."LEADID" ORDER BY la2."CREATIONDATE" DESC, la2."LEAD_ACTID" DESC), \'\') <> \'CANCELLED\'
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO"',
                array_merge([$compareStartStr, $compareEndStr], $dealerScopeBindings)
            );
        } else {
            $compareTotals = DB::select(
                'SELECT l."ASSIGNEDTO" AS dealer_id,
                        COUNT(*) AS total_c,
                        SUM(CASE WHEN (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                                       FROM "LEAD_ACT" la
                                       WHERE la."LEADID" = l."LEADID"
                                       ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                                      ) = \'FAILED\' THEN 1 ELSE 0 END) AS failed_c
                 FROM "LEAD" l
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   AND l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE)
                   AND COALESCE((SELECT FIRST 1 UPPER(TRIM(la2."STATUS")) FROM "LEAD_ACT" la2 WHERE la2."LEADID" = l."LEADID" ORDER BY la2."CREATIONDATE" DESC, la2."LEAD_ACTID" DESC), \'\') <> \'CANCELLED\'
                   AND l."CREATEDAT" <= CURRENT_DATE
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO"',
                array_merge([-$compareDays], $dealerScopeBindings)
            );
        }
        $compareByDealer = [];
        foreach ($compareTotals as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '') {
                continue;
            }
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
                'SELECT l."ASSIGNEDTO" AS dealer_id,
                        SUM(CASE WHEN l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP) THEN 1 ELSE 0 END) AS curr_c,
                        SUM(CASE WHEN l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP) THEN 1 ELSE 0 END) AS last_c
                 FROM "LEAD" l
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO"',
                array_merge([$primaryStartStr, $primaryEndStr, $compareStartStr, $compareEndStr], $dealerScopeBindings)
            );
        } else {
            $varianceRows = DB::select(
                'SELECT l."ASSIGNEDTO" AS dealer_id,
                        SUM(CASE WHEN l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE) AND l."CREATEDAT" <= CURRENT_DATE THEN 1 ELSE 0 END) AS curr_c,
                        SUM(CASE WHEN l."CREATEDAT" >= DATEADD(DAY, ?, DATEADD(YEAR, -1, CURRENT_DATE)) AND l."CREATEDAT" <= DATEADD(YEAR, -1, CURRENT_DATE) THEN 1 ELSE 0 END) AS last_c
                 FROM "LEAD" l
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO"',
                array_merge([-$days, -$days], $dealerScopeBindings)
            );
        }
        $variance = [];
        foreach ($varianceRows as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '' || ! isset($totalsByDealer[$id])) {
                continue;
            }
            $curr = (int) ($r->CURR_C ?? $r->curr_c ?? 0);
            $last = (int) ($r->LAST_C ?? $r->last_c ?? 0);
            $pct = $last > 0 ? (int) round(($curr - $last) / $last * 100) : ($curr > 0 ? 100 : 0);
            $variance[] = ['dealer_id' => $id, 'name' => $totalsByDealer[$id]['name'], 'delta' => $pct];
        }
        usort($variance, function ($a, $b) {
            return abs($b['delta']) <=> abs($a['delta']);
        });
        $variance = array_slice($variance, 0, 10);

        // Last activity per dealer (any lead activity)
        $lastActivityRows = DB::select(
            'SELECT l."ASSIGNEDTO" AS dealer_id,
                    MAX(a."CREATIONDATE") AS last_at
             FROM "LEAD_ACT" a
             JOIN "LEAD" l ON l."LEADID" = a."LEADID"
             WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
               '.$dealerScopeSql.'
             GROUP BY l."ASSIGNEDTO"',
            $dealerScopeBindings
        );
        $lastActivityByDealer = [];
        foreach ($lastActivityRows as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '') {
                continue;
            }
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
        // Dealer name from USERS via assignedTo; fail count & fail rate from LEAD (Failed) in period
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
                'SELECT l."ASSIGNEDTO" AS dealer_id,
                        COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL") AS name,
                        COUNT(*) AS failed_c
                 FROM "LEAD" l
                 JOIN "USERS" u ON u."USERID" = l."ASSIGNEDTO"
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   AND l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP)
                   AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                        FROM "LEAD_ACT" la
                        WHERE la."LEADID" = l."LEADID"
                        ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                       ) = \'FAILED\'
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO", COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL")
                 ORDER BY failed_c DESC',
                array_merge([$primaryStartStr, $primaryEndStr], $dealerScopeBindings)
            );
        } else {
            $failedRows = DB::select(
                'SELECT l."ASSIGNEDTO" AS dealer_id,
                        COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL") AS name,
                        COUNT(*) AS failed_c
                 FROM "LEAD" l
                 JOIN "USERS" u ON u."USERID" = l."ASSIGNEDTO"
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   AND l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE)
                   AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                        FROM "LEAD_ACT" la
                        WHERE la."LEADID" = l."LEADID"
                        ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                       ) = \'FAILED\'
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO", COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL")
                 ORDER BY failed_c DESC',
                array_merge([-$days], $dealerScopeBindings)
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
                'SELECT l."ASSIGNEDTO" AS dealer_id,
                        COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL") AS name,
                        COUNT(*) AS closed_c
                 FROM "LEAD" l
                 JOIN "USERS" u ON u."USERID" = l."ASSIGNEDTO"
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   AND l."CREATEDAT" >= CAST(? AS TIMESTAMP) AND l."CREATEDAT" <= CAST(? AS TIMESTAMP)
                   AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                        FROM "LEAD_ACT" la
                        WHERE la."LEADID" = l."LEADID"
                        ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                       ) IN (\'COMPLETED\', \'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\')
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO", COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL")
                 ORDER BY closed_c DESC',
                array_merge([$primaryStartStr, $primaryEndStr], $dealerScopeBindings)
            );
        } else {
            $closedRows = DB::select(
                'SELECT l."ASSIGNEDTO" AS dealer_id,
                        COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL") AS name,
                        COUNT(*) AS closed_c
                 FROM "LEAD" l
                 JOIN "USERS" u ON u."USERID" = l."ASSIGNEDTO"
                 WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                   AND l."CREATEDAT" >= DATEADD(DAY, ?, CURRENT_DATE)
                   AND (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                        FROM "LEAD_ACT" la
                        WHERE la."LEADID" = l."LEADID"
                        ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                       ) IN (\'COMPLETED\', \'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\')
                   '.$dealerScopeSql.'
                 GROUP BY l."ASSIGNEDTO", COALESCE(NULLIF(TRIM(u."COMPANY"), \'\'), u."EMAIL")
                 ORDER BY closed_c DESC',
                array_merge([-$days], $dealerScopeBindings)
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
            'primaryFrom' => $useCustomPrimary ? $primaryFrom : null,
            'primaryTo' => $useCustomPrimary ? $primaryTo : null,
            'compareFrom' => $useCustomCompare ? $compareFrom : null,
            'compareTo' => $useCustomCompare ? $compareTo : null,
            'compareDays' => $compareDays,
            'selectedArea' => $selectedArea,
            'areaOptions' => $areaOptions,
        ]);
    }

    /** Dealer activity (LEAD_ACT) for a dealer — view-only for Reports V2 "Log Intervention" popout */
    public function dealerActivity(string $userid): \Illuminate\Http\JsonResponse
    {
        $rows = DB::select(
            'SELECT a."LEAD_ACTID", a."LEADID", a."USERID", a."CREATIONDATE", a."SUBJECT", a."DESCRIPTION", a."STATUS"
             FROM "LEAD_ACT" a
             INNER JOIN "LEAD" l ON l."LEADID" = a."LEADID" AND l."ASSIGNEDTO" = ? AND COALESCE(l."ISDELETED", FALSE) = FALSE
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
        $selectedReportScope = $this->resolveAdminReportScope($request);
        $reportScopeOptions = $this->adminReportScopeOptions();
        $selectedArea = $this->resolveAdminReportArea($request);
        $areaOptions = $this->adminReportAreaOptions();

        $daysParam = $request->query('days', '60');
        $fromParam = trim((string) $request->query('from', ''));
        $toParam = trim((string) $request->query('to', ''));
        $useCustom = $fromParam !== '' && $toParam !== '';

        if ($useCustom) {
            try {
                $start = Carbon::parse($fromParam)->startOfDay();
                $end = Carbon::parse($toParam)->endOfDay();
                if ($start->gt($end)) {
                    $useCustom = false;
                } else {
                    $days = (int) max(1, $start->diffInDays($end) + 1);
                }
            } catch (\Throwable $e) {
                $useCustom = false;
            }
        }

        if (! $useCustom) {
            $days = (int) $daysParam;
            if (! in_array($days, [30, 60, 90], true)) {
                $days = 60;
            }
            $start = Carbon::now()->subDays($days - 1)->startOfDay();
            $end = Carbon::now()->endOfDay();
        }

        $periodLabel = $start->format('d/m/Y').' - '.$end->format('d/m/Y');

        $startStr = $start->format('Y-m-d H:i:s');
        $endStr = $end->format('Y-m-d H:i:s');
        [$leadScopeSql, $leadScopeBindings] = $this->buildAdminReportScopeSql(
            $selectedReportScope,
            'l."ASSIGNEDTO"',
            'u."COMPANY"'
        );

        if ($selectedArea) {
            $leadScopeSql .= ' AND UPPER(TRIM(u."CITY")) = ?';
            $leadScopeBindings[] = $selectedArea;
        }

        [$productScopeSql, $productScopeBindings] = $this->buildAdminReportScopeSql(
            $selectedReportScope,
            'a."USERID"',
            'u."COMPANY"'
        );

        // Dealer performance: total/closed/failed from LEAD_ACT; rewarded from LEAD_ACT (STATUS = Rewarded)
        $rowsSql = 'SELECT u."USERID" AS dealer_id,
                    u."EMAIL" AS email,
                    TRIM(COALESCE(u."COMPANY", \'\')) AS company,
                    TRIM(COALESCE(u."ALIAS", \'\')) AS alias,
                    COUNT(*) AS total_leads,
                    SUM(CASE WHEN (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                                   FROM "LEAD_ACT" la
                                   WHERE la."LEADID" = l."LEADID"
                                   ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                                  ) IN (\'COMPLETED\', \'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\') THEN 1 ELSE 0 END) AS closed_leads,
                    SUM(CASE WHEN (SELECT FIRST 1 UPPER(TRIM(la."STATUS"))
                                   FROM "LEAD_ACT" la
                                   WHERE la."LEADID" = l."LEADID"
                                   ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC
                                  ) = \'FAILED\' THEN 1 ELSE 0 END) AS failed_leads,
                    (SELECT COUNT(DISTINCT a."LEADID")
                     FROM "LEAD_ACT" a
                     INNER JOIN "LEAD" l2 ON l2."LEADID" = a."LEADID" AND l2."ASSIGNEDTO" = u."USERID"
                       AND COALESCE(l2."ISDELETED", FALSE) = FALSE
                       AND l2."CREATEDAT" >= ? AND l2."CREATEDAT" <= ?
                     WHERE UPPER(TRIM(COALESCE(a."STATUS", \'\'))) IN (\'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\')) AS rewarded_leads
             FROM "LEAD" l
              JOIN "USERS" u ON u."USERID" = l."ASSIGNEDTO"
               WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND l."ASSIGNEDTO" IS NOT NULL
                AND l."CREATEDAT" >= ?
                AND l."CREATEDAT" <= ?
                AND COALESCE((SELECT FIRST 1 UPPER(TRIM(la2."STATUS")) FROM "LEAD_ACT" la2 WHERE la2."LEADID" = l."LEADID" ORDER BY la2."CREATIONDATE" DESC, la2."LEAD_ACTID" DESC), \'\') <> \'CANCELLED\'
                '.$leadScopeSql.'
              GROUP BY u."USERID", u."EMAIL", u."COMPANY", u."ALIAS"
              ORDER BY total_leads DESC';
        $rowsBindings = [$startStr, $endStr, $startStr, $endStr];
        $rowsBindings = array_merge($rowsBindings, $leadScopeBindings);
        if ($selectedArea) {
            $rowsBindings[] = $selectedArea;
        }
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
             WHERE COALESCE(l."ISDELETED", FALSE) = FALSE AND a."USERID" IS NOT NULL
               AND UPPER(TRIM(COALESCE(a."USERID", \'\'))) = UPPER(TRIM(COALESCE(l."ASSIGNEDTO", \'\')))
               AND a."DEALTPRODUCT" IS NOT NULL
               AND TRIM(a."DEALTPRODUCT") <> \'\'
               AND UPPER(TRIM(COALESCE(a."STATUS", \'\'))) = ?
               AND a."CREATIONDATE" >= ?
               AND a."CREATIONDATE" <= ?
               '.$productScopeSql;
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
            if (! isset($topProductByDealer[$id])) {
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
            'days' => $days,
            'periodLabel' => $periodLabel,
            'selectedReportScope' => $selectedReportScope,
            'reportScopeOptions' => $reportScopeOptions,
            'from' => $useCustom ? $fromParam : null,
            'to' => $useCustom ? $toParam : null,
            'totalVolume' => $totalVolume,
            'avgRejectionRate' => $avgRejection,
            'topProductDealer' => $topProductDealer,
            'chartLabels' => $chartLabels,
            'chartVolume' => $chartVolume,
            'chartClosed' => $chartClosed,
            'chartRewarded' => $chartRewarded,
            'rankings' => $rankings,
            'selectedArea' => $selectedArea,
            'areaOptions' => $areaOptions,
        ]);
    }

    public function history(Request $request): View
    {
        $historyDateFilter = $this->resolveHistoryDateFilter($request);

        $where = 'WHERE a."CREATIONDATE" >= ? AND a."CREATIONDATE" <= ?';
        $bindings = [
            $historyDateFilter['rangeStart']->format('Y-m-d H:i:s'),
            $historyDateFilter['rangeEnd']->format('Y-m-d H:i:s'),
        ];

        // Load all matching rows for client-side pagination/filtering
        $rows = DB::select(
            "SELECT
                a.\"LEAD_ACTID\", a.\"LEADID\", a.\"USERID\", a.\"CREATIONDATE\", a.\"SUBJECT\", a.\"DESCRIPTION\", a.\"ATTACHMENT\", a.\"STATUS\",
                u.\"ALIAS\",
                l.\"POSTCODE\", l.\"CITY\", l.\"COMPANYNAME\", l.\"CONTACTNAME\"
            FROM \"LEAD_ACT\" a
            LEFT JOIN \"USERS\" u ON a.\"USERID\" = u.\"USERID\"
            LEFT JOIN \"LEAD\" l ON a.\"LEADID\" = l.\"LEADID\"
            $where
            ORDER BY \"LEAD_ACTID\" DESC",
            $bindings
        );

        $total = count($rows);
        $perPage = 10;
        $currentPageNum = 1;
        $lastPage = max(1, (int) ceil($total / $perPage));

        return view('admin.history', array_merge($historyDateFilter, [
            'items' => $rows,
            'total' => $total,
            'perPage' => $perPage,
            'currentPageNum' => $currentPageNum,
            'lastPage' => $lastPage,
            'currentPage' => 'history',
        ]));
    }

    private function resolveHistoryDateFilter(Request $request): array
    {
        $dateRange = strtolower(trim((string) $request->query('date_range', 'today')));
        $supportedRanges = ['today', 'yesterday', '2_days_ago', 'this_week', 'custom'];
        if (! in_array($dateRange, $supportedRanges, true)) {
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
            if ($startDateInput !== '' && $endDateInput !== '') {
                try {
                    $rangeStart = Carbon::parse($startDateInput)->startOfDay();
                    $rangeEnd = Carbon::parse($endDateInput)->endOfDay();
                } catch (\Throwable $e) {
                    $dateRange = 'today';
                }
            } else {
                // Default to 1 week ago if custom is selected but no dates provided yet
                $rangeStart = Carbon::now()->subDays(7)->startOfDay();
                $rangeEnd = Carbon::now()->endOfDay();
                $startDateInput = $rangeStart->format('Y-m-d');
                $endDateInput = $rangeEnd->format('Y-m-d');
            }

            if ($rangeStart->gt($rangeEnd)) {
                [$rangeStart, $rangeEnd] = [$rangeEnd, $rangeStart];
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

        if (! $row) {
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
            $like = '%'.$search.'%';
            $where[] = '('
                .'UPPER(TRIM(COALESCE(u."EMAIL", \'\'))) LIKE UPPER(?)'
                .' OR UPPER(TRIM(COALESCE(u."ALIAS", \'\'))) LIKE UPPER(?)'
                .' OR UPPER(TRIM(COALESCE(u."COMPANY", \'\'))) LIKE UPPER(?)'
                .')';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql = 'SELECT "USERID","EMAIL","SYSTEMROLE","ISACTIVE","ALIAS","COMPANY","POSTCODE","CITY","LASTLOGIN" FROM "USERS" u';
        if (! empty($where)) {
            $sql .= ' WHERE '.implode(' AND ', $where);
        }

        $rows = DB::select($sql, $params);
        $setupLinks = $this->setupLinkStore()->allSetupLinks();

        $protectedEmailsRows = DB::select(
            'SELECT DISTINCT UPPER(TRIM("EMAIL")) AS "EMAIL"
             FROM "USERS"
             WHERE "EMAIL" IS NOT NULL AND TRIM("EMAIL") != \'\' 
               AND "LASTLOGIN" IS NOT NULL'
        );
        $protectedEmailSet = [];
        foreach ($protectedEmailsRows as $pe) {
            $peEmail = $pe->EMAIL ?? $pe->email ?? '';
            if ($peEmail !== '') {
                $protectedEmailSet[$peEmail] = true;
            }
        }

        $users = array_map(function ($r) use ($setupLinks, $protectedEmailSet) {
            $userId = (string) ($r->USERID ?? '');
            $email = trim((string) ($r->EMAIL ?? ''));
            $emailUpper = strtoupper($email);
            
            $hasLoggedIn = $r->LASTLOGIN !== null;
            $isProtected = $hasLoggedIn || ($emailUpper !== '' && isset($protectedEmailSet[$emailUpper]));
            
            $setupLink = ! $isProtected && isset($setupLinks[$userId]) ? $setupLinks[$userId] : [];
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
                'HAS_LOGGED_IN' => $isProtected,
                'PASSKEY_SETUP_LINK_PENDING' => $setupLinkPending,
                'PASSKEY_SETUP_LINK_SENT' => $setupLinkEmailedAt !== '',
                'PASSKEY_SETUP_LINK_EXPIRED' => $setupLinkExpired,
                'PASSKEY_SETUP_LINK_EMAILED_AT' => $setupLinkEmailedAt !== '' ? $setupLinkEmailedAt : null,
                'PASSKEY_SETUP_LINK_EXPIRES_AT' => $setupLinkExpiresAt !== '' ? $setupLinkExpiresAt : null,
            ];
        }, $rows);

        usort($users, function ($a, $b) {
            $aId = $a['USERID'];
            $bId = $b['USERID'];
            $aNum = preg_match('/^U(\d+)$/i', $aId, $m) ? (int)$m[1] : $aId;
            $bNum = preg_match('/^U(\d+)$/i', $bId, $m) ? (int)$m[1] : $bId;
            
            if (is_int($aNum) && is_int($bNum)) {
                return $aNum <=> $bNum;
            }
            return strcmp((string)$aId, (string)$bId);
        });

        return $users;
    }

    private function maintainUsersBatchEligible(array $users): array
    {
        return array_values(array_filter($users, static function ($u) {
            $email = trim((string) ($u['EMAIL'] ?? ''));

            return ! ($u['HAS_LOGGED_IN'] ?? false)
                && (bool) ($u['ISACTIVE'] ?? false)
                && $email !== ''
                && ! str_ends_with(strtolower($email), '@noemail.local');
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

        if (! $isDealer) {
            $company = $estreamCompany;
            $postcode = '';
            $city = '';
        }

        $existing = DB::selectOne(
            'SELECT "USERID" FROM "USERS" WHERE UPPER(TRIM("EMAIL")) = UPPER(TRIM(?)) AND UPPER(TRIM("SYSTEMROLE")) = UPPER(TRIM(?))',
            [$email, $systemRole]
        );
        if ($existing) {
            return back()
                ->withInput()
                ->with('error', "User with this email already has the '{$systemRole}' role.");
        }

        $users = DB::select('SELECT "USERID" FROM "USERS" WHERE "USERID" STARTING WITH ?', ['U']);
        $maxNum = 0;
        foreach ($users as $u) {
            $uid = trim($u->USERID ?? $u->userid ?? '');
            $num = (int) substr($uid, 1);
            if ($num > $maxNum) {
                $maxNum = $num;
            }
        }
        $newUserId = 'U'.str_pad((string) ($maxNum + 1), 3, '0', STR_PAD_LEFT);

        DB::insert(
            'INSERT INTO "USERS" ("USERID","EMAIL","PASSWORDHASH","SYSTEMROLE","ISACTIVE","ALIAS","COMPANY","POSTCODE","CITY") VALUES (?,?,?,?,?,?,?,?,?)',
            [
                $newUserId,
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
            'SELECT "USERID" FROM "USERS" WHERE UPPER(TRIM("EMAIL")) = UPPER(TRIM(?)) AND UPPER(TRIM("SYSTEMROLE")) = UPPER(TRIM(?))',
            [$email, $systemRole]
        );

        $createAction = trim((string) $request->input('CREATE_ACTION', 'create'));
        if ($createAction === 'create_email' && $createdUser && trim((string) ($createdUser->USERID ?? '')) !== '') {
            $driver = DB::connection()->getDriverName();
            $activeClause = ($driver === 'sqlsrv') 
                ? '"ISACTIVE" = 1 OR "ISACTIVE" IS NULL' 
                : '"ISACTIVE" = true OR "ISACTIVE" IS NULL';

            $isProtected = DB::selectOne(
                'SELECT FIRST 1 "USERID" FROM "USERS" WHERE UPPER(TRIM("EMAIL")) = UPPER(TRIM(?)) AND "LASTLOGIN" IS NOT NULL AND (' . $activeClause . ')',
                [$email]
            );

            if ($isProtected) {
                return redirect()->route('admin.maintain-users')->with('success', 'User created succesfully, Setup link not sent because this user alraedy have existing passkey');
            }

            try {
                $newUser = $this->loadMaintainUserPasskeyTarget((string) $createdUser->USERID);

                if (! $newUser || ! $this->sendMaintainUserPasskeySetupLink($newUser)) {
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
        if (! $existing) {
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

        if (! $isDealer) {
            $company = $estreamCompany;
            $postcode = '';
            $city = '';
        }

        // Email + Role unique except current user
        $roleValue = $existing->SYSTEMROLE ?? '';
        $emailConflict = DB::selectOne(
            'SELECT "USERID" FROM "USERS" WHERE UPPER(TRIM("EMAIL")) = UPPER(TRIM(?)) AND UPPER(TRIM("SYSTEMROLE")) = UPPER(TRIM(?)) AND "USERID" <> ?',
            [$email, $roleValue, $userid]
        );
        if ($emailConflict) {
            return back()->withInput()->with('error', "Another user already has this email with the '{$roleValue}' role.");
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
            return '"'.str_replace('"', '""', $name).'"';
        };
        $emailCol = $col('EMAIL');
        $aliasCol = $col('ALIAS');
        $companyCol = $col('COMPANY');
        $postcodeCol = $col('POSTCODE');
        $cityCol = $col('CITY');
        $activeCol = $col('ISACTIVE');
        $idCol = $col('USERID');
        if (! $emailCol || ! $activeCol || ! $idCol) {
            return back()->withInput()->with('error', 'User settings could not be loaded. Please contact support if this continues.');
        }

        $isActiveValue = $isActive ? 1 : 0;
        try {
            $parts = [$q($emailCol).' = ?'];
            $bind = [$email];
            if ($aliasCol) {
                $parts[] = $q($aliasCol).' = ?';
                $bind[] = $alias !== '' ? $alias : null;
            }
            if ($companyCol) {
                $parts[] = $q($companyCol).' = ?';
                $bind[] = $company !== '' ? $company : null;
            }
            if ($postcodeCol) {
                $parts[] = $q($postcodeCol).' = ?';
                $bind[] = $postcode !== '' ? $postcode : '';
            }
            if ($cityCol) {
                $parts[] = $q($cityCol).' = ?';
                $bind[] = $city !== '' ? $city : '';
            }
            $parts[] = $q($activeCol).' = ?';
            $bind[] = $isActiveValue;
            $bind[] = $userid;
            DB::update(
                'UPDATE "USERS" SET '.implode(', ', $parts).' WHERE '.$q($idCol).' = ?',
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
                if (! $updatedUser || trim((string) ($updatedUser->EMAIL ?? '')) === '') {
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

        if (! $user) {
            return redirect()->route('admin.maintain-users')->with('error', 'User not found.');
        }

        // Allow sending setup links to all users, acts as a reset if they have already logged in

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
               AND "USERID" IN ('.$placeholders.')
             ORDER BY "USERID"',
            $selectedUserIds
        );

        $sent = 0;
        $seenEmails = [];

        foreach ($users as $user) {
            $emailRaw = trim((string) ($user->EMAIL ?? ''));
            $emailLower = strtolower($emailRaw);
            
            // Skip if we already sent a setup link to this email address in this batch
            if ($emailLower !== '' && !isset($seenEmails[$emailLower])) {
                $seenEmails[$emailLower] = true;
                try {
                    if ($this->sendMaintainUserPasskeySetupLink($user)) {
                        $sent++;
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        if ($sent === 0) {
            return redirect()->route('admin.maintain-users')->with('error', 'No eligible users found for passkey setup link email.');
        }

        return redirect()->route('admin.maintain-users')->with('success', 'Passkey setup link emailed to '.$sent.' user(s).');
    }

    private function sendMaintainUserPasskeySetupLink(object $user, ?string $introLine = null): bool
    {
        $userId = trim((string) ($user->USERID ?? ''));
        $emailRaw = trim((string) ($user->EMAIL ?? ''));
        if ($userId === '' || $emailRaw === '' || str_ends_with(strtolower($emailRaw), '@noemail.local')) {
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

        // Send setup link only to the first email address (supporting space, comma, semicolon delimiters)
        $emails = array_filter(array_map('trim', preg_split('/[\s,;]+/', $emailRaw)), fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

        if (empty($emails)) {
            return false;
        }

        $singleEmail = array_values($emails)[0];

        Mail::to($singleEmail)->send(new UserPasskeySetupLink(
            toEmail: $singleEmail,
            recipientName: $recipientName,
            setupUrl: route('passkey.setup.form', ['token' => $token, 'e' => $singleEmail]),
            systemName: $systemName,
            subjectLine: 'Set up your SQL SMS passkey',
            introLine: $introLine ?? 'Your SQL SMS account is ready.',
            instructionLine: 'Click the link below to start setting up your passkey:',
            buttonLabel: 'Set up passkey',
            expiryLine: 'This link will expire in 24 hours.',
            ignoreLine: ''
        ));

        $this->setupLinkStore()->markSetupTokenEmailed($userId);

        // Reset the user's passkey status by wiping existing passkeys
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::delete('DELETE FROM "User_Passkey" WHERE "UserID" = ?', [$userId]);
        } elseif ($driver === 'sqlsrv') {
            DB::delete('DELETE FROM [User_Passkey] WHERE [UserID] = ?', [$userId]);
        } elseif ($driver === 'firebird') {
            DB::delete('DELETE FROM "USER_PASSKEY" WHERE "USERID" = ?', [$userId]);
        } else {
            DB::table('User_Passkey')->where('UserID', $userId)->delete();
        }
        DB::update('UPDATE "USERS" SET "LASTLOGIN" = NULL WHERE "USERID" = ?', [$userId]);

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
     * @param  string  $dealerUserId  USERS.USERID of the assigned dealer
     * @param  int  $leadId  LEAD.LEADID
     * @param  string|null  $companyName  Optional; if null, fetched from LEAD
     * @param  string|null  $contactName  Optional; if null, fetched from LEAD
     */
    private function sendInquiryAssignedEmail(string $dealerUserId, int $leadId, ?string $companyName = null, ?string $contactName = null): void
    {
        try {
            $dealer = DB::selectOne(
                'SELECT "EMAIL", "ALIAS", "COMPANY" FROM "USERS" WHERE CAST("USERID" AS VARCHAR(50)) = ?',
                [$dealerUserId]
            );
            if (! $dealer || empty(trim((string) ($dealer->EMAIL ?? '')))) {
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

            $lead = DB::selectOne('SELECT "COMPANYNAME", "CONTACTNAME", "EMAIL" FROM "LEAD" WHERE "LEADID" = ?', [$leadId]);
            $customerEmail = $lead ? trim((string) ($lead->EMAIL ?? '')) : '';

            if ($companyName === null || $contactName === null) {
                $companyName = $lead ? trim((string) ($lead->COMPANYNAME ?? '')) : '';
                $contactName = $lead ? trim((string) ($lead->CONTACTNAME ?? '')) : '';
            }
            $companyName = $companyName !== '' ? $companyName : '—';
            $contactName = $contactName !== '' ? $contactName : '—';

            $viewInquiryUrl = url(route('dealer.inquiries', [], false).'?lead='.$leadId);

            // Send assignment email to dealer
            Mail::to($dealerEmail)->send(new InquiryAssignedToDealer(
                dealerEmail: $dealerEmail,
                dealerName: $dealerName,
                leadId: $leadId,
                inquiryId: 'SQL-'.$leadId,
                companyName: $companyName,
                contactName: $contactName,
                viewInquiryUrl: $viewInquiryUrl
            ));

            // Send assignment email to customer
            if ($customerEmail !== '') {
                Mail::to($customerEmail)->send(new InquiryAssignedToCustomer(
                    customerEmail: $customerEmail,
                    customerName: $contactName !== '—' ? $contactName : 'Customer',
                    inquiryId: 'SQL-'.$leadId,
                    dealerName: $dealerName,
                    dealerEmail: $dealerEmail
                ));
            }
        } catch (\Throwable $e) {
            // Log but do not fail the request (assignment already succeeded)
            report($e);
        }
    }

    private function getDuplicateLeadIds(): array
    {
        $duplicateLeadIds = [];
        try {
            $dupKeys = DB::select(
                'SELECT LOWER(TRIM("COMPANYNAME")) AS "comp", LOWER(TRIM("CONTACTNO")) AS "phone", LOWER(TRIM("EMAIL")) AS "email_addr", COUNT(*) AS "cnt"
                 FROM "LEAD"
                 WHERE COALESCE("ISDELETED", FALSE) = FALSE
                 GROUP BY LOWER(TRIM("COMPANYNAME")), LOWER(TRIM("CONTACTNO")), LOWER(TRIM("EMAIL"))
                 HAVING COUNT(*) > 1'
            );

            if (! empty($dupKeys)) {
                foreach ($dupKeys as $k) {
                    $comp = $k->comp ?? '';
                    $phone = $k->phone ?? '';
                    $email_addr = $k->email_addr ?? '';

                    $leads = DB::select(
                        'SELECT "LEADID", "ASSIGNEDTO" FROM "LEAD"
                         WHERE COALESCE("ISDELETED", FALSE) = FALSE
                           AND LOWER(TRIM("COMPANYNAME")) = ?
                           AND LOWER(TRIM("CONTACTNO")) = ?
                           AND LOWER(TRIM("EMAIL")) = ?',
                        [$comp, $phone, $email_addr]
                    );

                    $hasUnassigned = false;
                    foreach ($leads as $l) {
                        if (empty(trim((string) ($l->ASSIGNEDTO ?? '')))) {
                            $hasUnassigned = true;
                            break;
                        }
                    }

                    if ($hasUnassigned && count($leads) > 1) {
                        usort($leads, function ($a, $b) {
                            return (int) $a->LEADID <=> (int) $b->LEADID;
                        });
                        for ($i = 1; $i < count($leads); $i++) {
                            $duplicateLeadIds[(int) $leads[$i]->LEADID] = true;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return $duplicateLeadIds;
    }
}
