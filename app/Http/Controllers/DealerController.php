<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesInquiryAttachments;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DealerController extends Controller
{
    use ResolvesInquiryAttachments;

    public function dashboard(Request $request): View
    {
        $dealerId = $request->session()->get('user_id');
        $leads = [];
        $metrics = [
            'activeInquiries' => 0,
            'pctActive' => 0,
            'conversionRate' => 0,
            'conversionTrend' => 0,
            'closedCaseCount' => 0,
            'pctClosed' => 0,
            'pendingFollowups' => 0,
        ];
        $closedCaseChartData = [
            'chartLabels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'chartData' => array_fill(0, 7, 0),
            'chartMonthLabels' => range(1, 30),
            'chartMonthData' => array_fill(0, 30, 0),
            'chartYearLabels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'chartYearData' => array_fill(0, 12, 0),
        ];
        $highPriorityFollowups = [];
        $leadsTotal = 0;
        $inquiriesPage = 1;
        $inquiriesTotalPages = 1;
        $inquiriesPerPage = 8;
        $leadsPaginated = [];
        $activeInquiriesCount = 0;
        $closedCount = 0;
        $conversion = 0.0;
        $conversionTrend = 0.0;
        $pendingFollowupsCount = 0;
        $pctActive = 0.0;
        $pctClosed = 0.0;

        if ($dealerId) {
            $dealerEmail = trim((string) ($request->session()->get('user_email') ?? ''));
            if (!$dealerEmail) {
                $emailRow = DB::selectOne('SELECT "EMAIL" FROM "USERS" WHERE "USERID" = ?', [$dealerId]);
                $dealerEmail = trim((string) ($emailRow->EMAIL ?? ''));
            }

            $leadsRaw = DB::select(
                'SELECT FIRST 200
                    l."LEADID", l."PRODUCTID", l."COMPANYNAME", l."CONTACTNAME", l."CONTACTNO", l."EMAIL",
                    l."CITY", l."POSTCODE", l."BUSINESSNATURE", l."USERCOUNT", l."EXISTINGSOFTWARE", l."DEMOMODE",
                    l."DESCRIPTION", l."REFERRALCODE", l."CREATEDAT", l."CREATEDBY",
                    l."ASSIGNED_TO", l."LASTMODIFIED",
                    u."EMAIL" AS "ASSIGNED_BY_EMAIL",
                    COALESCE(
                        (SELECT FIRST 1 la."STATUS"
                           FROM "LEAD_ACT" la
                          WHERE la."LEADID" = l."LEADID"
                          ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                        l."CURRENTSTATUS",
                        \'Pending\'
                    ) AS "ACT_STATUS",
                    (SELECT FIRST 1 la."CREATIONDATE"
                       FROM "LEAD_ACT" la
                      WHERE la."LEADID" = l."LEADID"
                      ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC) AS "ACT_LAST_UPDATE"
                FROM "LEAD" l
                LEFT JOIN "USERS" u ON u."USERID" = l."CREATEDBY"
                WHERE l."ASSIGNED_TO" = ?
                ORDER BY l."LEADID" DESC',
                [$dealerId]
            );
            $allAssignedLeads = array_values(array_filter($leadsRaw, function ($l) {
                $s = strtoupper(trim($l->ACT_STATUS ?? $l->CURRENTSTATUS ?? ''));
                return $s !== 'FAILED';
            }));
            $leads = DB::select(
                'SELECT FIRST 200
                    l."LEADID", l."PRODUCTID", l."COMPANYNAME", l."CONTACTNAME", l."CONTACTNO", l."EMAIL",
                    l."CITY", l."POSTCODE", l."BUSINESSNATURE", l."USERCOUNT", l."EXISTINGSOFTWARE", l."DEMOMODE",
                    l."DESCRIPTION", l."REFERRALCODE", l."CREATEDAT", l."CREATEDBY",
                    l."ASSIGNED_TO", l."LASTMODIFIED",
                    u."EMAIL" AS "ASSIGNED_BY_EMAIL",
                    COALESCE(
                        (SELECT FIRST 1 la."STATUS"
                           FROM "LEAD_ACT" la
                          WHERE la."LEADID" = l."LEADID"
                          ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                        l."CURRENTSTATUS",
                        \'Pending\'
                    ) AS "ACT_STATUS",
                    (SELECT FIRST 1 la."CREATIONDATE"
                       FROM "LEAD_ACT" la
                      WHERE la."LEADID" = l."LEADID"
                      ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC) AS "ACT_LAST_UPDATE"
                FROM "LEAD" l
                LEFT JOIN "USERS" u ON u."USERID" = l."CREATEDBY"
                WHERE l."ASSIGNED_TO" = ?
                  AND UPPER(TRIM(COALESCE(l."CURRENTSTATUS", \'\'))) = ?
                ORDER BY l."LEADID" DESC',
                [$dealerId, 'ONGOING']
            );

            $activeCountRow = DB::selectOne(
                'SELECT COUNT(*) AS "CNT" FROM "LEAD"
                WHERE "ASSIGNED_TO" = ? AND UPPER(TRIM(COALESCE("CURRENTSTATUS", \'\'))) = ?',
                [$dealerId, 'ONGOING']
            );
            $activeInquiriesCount = (int) ($activeCountRow->CNT ?? 0);

            // Total closed should only count leads whose latest status for this dealer
            // is COMPLETED or REWARDED (and not later marked as FAILED).
            $closedCountRow = DB::selectOne(
                'SELECT COUNT(*) AS "CNT"
                 FROM (
                     SELECT DISTINCT la."LEADID",
                         (
                             SELECT FIRST 1 la2."STATUS"
                             FROM "LEAD_ACT" la2
                             WHERE la2."LEADID" = la."LEADID"
                               AND la2."USERID" = la."USERID"
                             ORDER BY la2."CREATIONDATE" DESC, la2."LEAD_ACTID" DESC
                         ) AS "LATEST_STATUS"
                     FROM "LEAD_ACT" la
                     WHERE la."USERID" = ?
                 ) x
                 WHERE UPPER(TRIM(COALESCE(x."LATEST_STATUS", \'\'))) IN (\'COMPLETED\', \'REWARDED\')',
                [$dealerId]
            );
            $closedCount = (int) ($closedCountRow->CNT ?? 0);

            $countMetricRow = static function ($row): int {
                return (int) ($row->CNT ?? $row->cnt ?? $row->C ?? $row->c ?? current((array) $row) ?? 0);
            };

            $activeThisWeek = $activeInquiriesCount;
            $activeLastWeek = 0;
            $closedThisWeek = 0;
            $closedLastWeek = 0;

            try {
                $startThisWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
                $endLastWeek = $startThisWeek->copy()->subSecond();
                $startLastWeek = $startThisWeek->copy()->subWeek();

                $countActiveSnapshot = function (string $cutoff) use ($dealerId, $countMetricRow): int {
                    $row = DB::selectOne(
                        'SELECT COUNT(*) AS "CNT"
                         FROM (
                             SELECT l."LEADID",
                                    COALESCE(
                                        (SELECT FIRST 1 la."STATUS"
                                         FROM "LEAD_ACT" la
                                         WHERE la."LEADID" = l."LEADID"
                                           AND la."CREATIONDATE" <= ?
                                         ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                                        l."CURRENTSTATUS",
                                        \'Pending\'
                                    ) AS "LATEST_STATUS"
                             FROM "LEAD" l
                             WHERE l."ASSIGNED_TO" = ?
                               AND l."CREATEDAT" <= ?
                         ) x
                         WHERE UPPER(TRIM(COALESCE(x."LATEST_STATUS", \'\'))) IN (\'PENDING\', \'FOLLOWUP\', \'FOLLOW UP\', \'DEMO\', \'CONFIRMED\', \'CASE CONFIRMED\', \'ONGOING\')',
                        [$cutoff, $dealerId, $cutoff]
                    );

                    return $countMetricRow($row);
                };

                $activeLastWeek = $countActiveSnapshot($endLastWeek->format('Y-m-d H:i:s'));

                $closedThisWeekRow = DB::selectOne(
                    'SELECT COUNT(DISTINCT "LEADID") AS "CNT"
                     FROM "LEAD_ACT"
                     WHERE "USERID" = ?
                       AND UPPER(TRIM(COALESCE("STATUS", \'\'))) IN (\'COMPLETED\', \'REWARDED\', \'REWARD DISTRIBUTED\')
                       AND "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?',
                    [$dealerId, $startThisWeek->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')]
                );
                $closedThisWeek = $countMetricRow($closedThisWeekRow);

                $closedLastWeekRow = DB::selectOne(
                    'SELECT COUNT(DISTINCT "LEADID") AS "CNT"
                     FROM "LEAD_ACT"
                     WHERE "USERID" = ?
                       AND UPPER(TRIM(COALESCE("STATUS", \'\'))) IN (\'COMPLETED\', \'REWARDED\', \'REWARD DISTRIBUTED\')
                       AND "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?',
                    [$dealerId, $startLastWeek->format('Y-m-d H:i:s'), $endLastWeek->format('Y-m-d H:i:s')]
                );
                $closedLastWeek = $countMetricRow($closedLastWeekRow);
            } catch (\Throwable $e) {
                $activeLastWeek = 0;
                $closedThisWeek = 0;
                $closedLastWeek = 0;
            }

            $pctActive = $activeLastWeek > 0
                ? round((($activeThisWeek - $activeLastWeek) / $activeLastWeek) * 100, 1)
                : ($activeThisWeek > 0 ? 100 : 0);
            $pctClosed = $closedLastWeek > 0
                ? round((($closedThisWeek - $closedLastWeek) / $closedLastWeek) * 100, 1)
                : ($closedThisWeek > 0 ? 100 : 0);

            $countConversionLeads = function (?string $cutoff = null) use ($dealerId, $countMetricRow): int {
                $latestStatusCutoff = '';
                $leadCreatedCutoff = '';
                $params = [$dealerId];

                if ($cutoff !== null) {
                    $latestStatusCutoff = ' AND la."CREATIONDATE" <= ?';
                    $leadCreatedCutoff = ' AND l."CREATEDAT" <= ?';
                    $params = [$cutoff, $dealerId, $cutoff];
                }

                $row = DB::selectOne(
                    'SELECT COUNT(*) AS "CNT"
                     FROM (
                         SELECT l."LEADID",
                                COALESCE(
                                    (SELECT FIRST 1 la."STATUS"
                                     FROM "LEAD_ACT" la
                                     WHERE la."LEADID" = l."LEADID"' . $latestStatusCutoff . '
                                     ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                                    l."CURRENTSTATUS",
                                    \'Pending\'
                                ) AS "LATEST_STATUS"
                         FROM "LEAD" l
                         WHERE l."ASSIGNED_TO" = ?' . $leadCreatedCutoff . '
                     ) x
                     WHERE UPPER(TRIM(COALESCE(x."LATEST_STATUS", \'\'))) <> \'FAILED\'',
                    $params
                );

                return $countMetricRow($row);
            };

            $countConvertedLeads = function (?string $cutoff = null) use ($dealerId, $countMetricRow): int {
                $latestStatusCutoff = '';
                $leadCreatedCutoff = '';
                $params = [$dealerId];

                if ($cutoff !== null) {
                    $latestStatusCutoff = ' AND la."CREATIONDATE" <= ?';
                    $leadCreatedCutoff = ' AND l."CREATEDAT" <= ?';
                    $params = [$cutoff, $dealerId, $cutoff];
                }

                $row = DB::selectOne(
                    'SELECT COUNT(*) AS "CNT"
                     FROM (
                         SELECT l."LEADID",
                                COALESCE(
                                    (SELECT FIRST 1 la."STATUS"
                                     FROM "LEAD_ACT" la
                                     WHERE la."LEADID" = l."LEADID"' . $latestStatusCutoff . '
                                     ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                                    l."CURRENTSTATUS",
                                    \'Pending\'
                                ) AS "LATEST_STATUS"
                         FROM "LEAD" l
                         WHERE l."ASSIGNED_TO" = ?' . $leadCreatedCutoff . '
                     ) x
                     WHERE UPPER(TRIM(COALESCE(x."LATEST_STATUS", \'\'))) IN (\'COMPLETED\', \'REWARDED\')',
                    $params
                );

                return $countMetricRow($row);
            };

            $conversionLeadCount = $countConversionLeads();
            $conversionClosedCount = $countConvertedLeads();
            $conversion = $conversionLeadCount > 0 ? round(($conversionClosedCount / $conversionLeadCount) * 100, 1) : 0;
            $conversionTrend = 0.0;

            try {
                $endLastWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->subSecond()->format('Y-m-d H:i:s');
                $conversionLeadCountLastWeek = $countConversionLeads($endLastWeek);
                $conversionClosedCountLastWeek = $countConvertedLeads($endLastWeek);
                $conversionRateLastWeek = $conversionLeadCountLastWeek > 0
                    ? ($conversionClosedCountLastWeek / $conversionLeadCountLastWeek) * 100
                    : 0;
                $conversionTrend = round($conversion - $conversionRateLastWeek, 1);
            } catch (\Throwable $e) {
                $conversionTrend = 0.0;
            }

            $pendingFollowupsSql = $dealerEmail
                ? 'SELECT COUNT(*) AS "CNT" FROM "LEAD"
                  WHERE "ASSIGNED_TO" = (SELECT "USERID" FROM "USERS" WHERE "EMAIL" = ?)
                  AND UPPER(TRIM(COALESCE("CURRENTSTATUS", \'\'))) = ?'
                : 'SELECT COUNT(*) AS "CNT" FROM "LEAD"
                  WHERE "ASSIGNED_TO" = ? AND UPPER(TRIM(COALESCE("CURRENTSTATUS", \'\'))) = ?';
            $pendingFollowupsParams = $dealerEmail ? [$dealerEmail, 'ONGOING'] : [$dealerId, 'ONGOING'];
            $pendingFollowupsRow = DB::selectOne($pendingFollowupsSql, $pendingFollowupsParams);
            $pendingFollowupsCount = (int) ($pendingFollowupsRow->CNT ?? 0);

            $now = time();
            $weekStart = date('Y-m-d', strtotime('monday this week', $now));
            $weekEnd = date('Y-m-d 23:59:59', strtotime('sunday this week', $now));
            $monthStart = date('Y-m-d', strtotime('first day of this month', $now));
            $monthEnd = date('Y-m-d 23:59:59', strtotime('last day of this month', $now));
            $yearStart = date('Y') . '-01-01';
            $yearEnd = date('Y') . '-12-31 23:59:59';

            $chartLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $chartData = array_fill(0, 7, 0);
            $chartMonthLabels = [];
            $chartMonthData = [];
            $chartYearLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $chartYearData = array_fill(0, 12, 0);

            try {
                $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
                for ($i = 0; $i < 7; $i++) {
                    $day = $startOfWeek->copy()->addDays($i)->format('Y-m-d');
                    $r = DB::selectOne(
                        'SELECT COUNT(*) AS c FROM "LEAD_ACT"
                        WHERE "USERID" = ? AND UPPER(TRIM(COALESCE("STATUS", \'\'))) IN (\'COMPLETED\', \'REWARDED\')
                        AND CAST("CREATIONDATE" AS DATE) = CAST(? AS DATE)',
                        [$dealerId, $day]
                    );
                    $chartData[$i] = (int) ($r->c ?? $r->C ?? 0);
                }
            } catch (\Throwable $e) {
                // keep zeros
            }

            try {
                $start = Carbon::now()->startOfMonth();
                $daysInMonth = $start->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $chartMonthLabels[] = (string) $i;
                    $day = $start->copy()->day($i)->format('Y-m-d');
                    $r = DB::selectOne(
                        'SELECT COUNT(*) AS c FROM "LEAD_ACT"
                        WHERE "USERID" = ? AND UPPER(TRIM(COALESCE("STATUS", \'\'))) IN (\'COMPLETED\', \'REWARDED\')
                        AND CAST("CREATIONDATE" AS DATE) = CAST(? AS DATE)',
                        [$dealerId, $day]
                    );
                    $chartMonthData[] = (int) ($r->c ?? $r->C ?? 0);
                }
            } catch (\Throwable $e) {
                $chartMonthLabels = range(1, 30);
                $chartMonthData = array_fill(0, count($chartMonthLabels), 0);
            }

            try {
                $yearStart = Carbon::now()->startOfYear();
                for ($m = 0; $m < 12; $m++) {
                    $monthStart = $yearStart->copy()->addMonths($m);
                    $monthEnd = $monthStart->copy()->endOfMonth();
                    $r = DB::selectOne(
                        'SELECT COUNT(*) AS c FROM "LEAD_ACT"
                        WHERE "USERID" = ? AND UPPER(TRIM(COALESCE("STATUS", \'\'))) IN (\'COMPLETED\', \'REWARDED\')
                        AND "CREATIONDATE" >= ? AND "CREATIONDATE" <= ?',
                        [$dealerId, $monthStart->format('Y-m-d 00:00:00'), $monthEnd->format('Y-m-d 23:59:59')]
                    );
                    $chartYearData[$m] = (int) ($r->c ?? $r->C ?? 0);
                }
            } catch (\Throwable $e) {
                $chartYearData = array_fill(0, 12, 0);
            }

            $closedCaseChartData = [
                'chartLabels' => $chartLabels,
                'chartData' => $chartData,
                'chartMonthLabels' => $chartMonthLabels,
                'chartMonthData' => $chartMonthData,
                'chartYearLabels' => $chartYearLabels,
                'chartYearData' => $chartYearData,
            ];

            $metrics = [
                'activeInquiries' => $activeInquiriesCount,
                'pctActive' => $pctActive,
                'conversionRate' => $conversion,
                'conversionTrend' => $conversionTrend,
                'closedCaseCount' => $closedCount,
                'pctClosed' => $pctClosed,
                'pendingFollowups' => $pendingFollowupsCount,
            ];

            $statusMap = [
                'PENDING' => 'PENDING', 'FOLLOW UP' => 'FOLLOW UP', 'FOLLOWUP' => 'FOLLOW UP',
                'DEMO' => 'DEMO', 'CONFIRMED' => 'CONFIRMED', 'CASE CONFIRMED' => 'CONFIRMED',
                'COMPLETED' => 'COMPLETED', 'CASE COMPLETED' => 'COMPLETED',
                'REWARD' => 'REWARDED', 'REWARDED' => 'REWARDED', 'REWARD DISTRIBUTED' => 'REWARDED',
            ];
            $stages = ['PENDING', 'FOLLOW UP', 'DEMO', 'CONFIRMED', 'COMPLETED', 'REWARDED'];
            $now = time();
            $highPriorityFollowups = collect($leads)
                ->filter(function ($l) use ($stages, $statusMap) {
                    $raw = strtoupper(trim($l->ACT_STATUS ?? $l->CURRENTSTATUS ?? 'PENDING'));
                    $status = $statusMap[$raw] ?? 'PENDING';
                    $idx = array_search($status, $stages);
                    $idx = $idx !== false ? $idx : 0;
                    return $idx < 4;
                })
                ->map(function ($l) use ($now) {
                    $lastAct = $l->ACT_LAST_UPDATE ?? $l->LASTMODIFIED ?? null;
                    $lastMod = $lastAct ? strtotime($lastAct) : $now;
                    $nextFollowUp = strtotime('+3 days', $lastMod);
                    $diffSec = $now - $nextFollowUp;

                    if ($diffSec > 0) {
                        $status = 'OVERDUE';
                        $mins = (int) floor($diffSec / 60);
                        $hours = (int) floor($diffSec / 3600);
                        $days = (int) floor($diffSec / 86400);
                        if ($mins < 60) {
                            $time = max(1, $mins) . 'm';
                        } elseif ($hours < 24) {
                            $time = $hours . 'h';
                        } else {
                            $time = $days . 'd';
                        }
                    } else {
                        $status = 'DUE SOON';
                        $untilSec = $nextFollowUp - $now;
                        $mins = max(0, (int) floor($untilSec / 60));
                        $hours = max(0, (int) floor($untilSec / 3600));
                        $days = max(0, (int) floor($untilSec / 86400));
                        if ($mins < 60) {
                            $time = max(1, $mins) . 'm';
                        } elseif ($hours < 24) {
                            $time = $hours . 'h';
                        } elseif ($days < 2) {
                            $time = '1d';
                        } else {
                            $time = $days . 'd';
                        }
                    }

                    $contact = $l->CONTACTNAME ? 'Ms/Mr ' . explode(' ', trim($l->CONTACTNAME))[0] : '—';
                    return (object) [
                        'leadId' => $l->LEADID,
                        'status' => $status,
                        'time' => $time,
                        'inquiryId' => 'SQL-' . $l->LEADID,
                        'contact' => $contact,
                        'product' => $l->COMPANYNAME ?: 'SQL Account + Stock',
                        'email' => trim((string) ($l->EMAIL ?? '')),
                        '_sortOrder' => $diffSec,
                    ];
                })
                ->filter(function ($item) {
                    $diffSec = $item->_sortOrder;
                    return $diffSec > 0 || $diffSec >= -172800;
                })
                ->sortBy('_sortOrder', SORT_REGULAR, true)
                ->map(fn($item) => (object) [
                    'leadId' => $item->leadId,
                    'status' => $item->status,
                    'time' => $item->time,
                    'inquiryId' => $item->inquiryId,
                    'contact' => $item->contact,
                    'product' => $item->product,
                    'email' => trim((string) ($item->email ?? '')),
                ])
                ->values()
                ->all();
        }

        $inquiriesPerPage = 8;
        $leadsTotal = count($leads);
        $inquiriesTotalPages = max(1, (int) ceil($leadsTotal / $inquiriesPerPage));

        return view('dealer.dashboard', [
            'leads' => $leads,
            'leadsTotal' => $leadsTotal,
            'inquiriesTotalPages' => $inquiriesTotalPages,
            'inquiriesPerPage' => $inquiriesPerPage,
            'metrics' => $metrics,
            'closedCaseChartData' => $closedCaseChartData,
            'highPriorityFollowups' => $highPriorityFollowups,
            'currentPage' => 'dashboard',
        ]);
    }

    public function inquiries(Request $request): View
    {
        $dealerId = $request->session()->get('user_id');
        $focusLeadId = (int) $request->query('lead', 0);
        $dealerConsoleCounts = $this->getDealerConsoleCounts($dealerId);
        $leads = [];
        if ($dealerId) {
            $dealerInquiriesSql = 'SELECT FIRST 200
                    l."LEADID", l."PRODUCTID", l."COMPANYNAME", l."CONTACTNAME", l."CONTACTNO", l."EMAIL",
                    l."ADDRESS1", l."ADDRESS2", l."CITY", l."POSTCODE", l."BUSINESSNATURE", l."USERCOUNT",
                    l."EXISTINGSOFTWARE", l."DEMOMODE", l."DESCRIPTION", l."REFERRALCODE", l."CREATEDAT", l."CREATEDBY",
                    l."ASSIGNED_TO", l."LASTMODIFIED",
                    u."EMAIL" AS "ASSIGNED_BY_EMAIL",
                    COALESCE(
                        (SELECT FIRST 1 la."STATUS"
                           FROM "LEAD_ACT" la
                          WHERE la."LEADID" = l."LEADID"
                          ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                        l."CURRENTSTATUS",
                        \'Pending\'
                    ) AS "ACT_STATUS"
                FROM "LEAD" l
                LEFT JOIN "USERS" u ON u."USERID" = l."CREATEDBY"
                WHERE l."ASSIGNED_TO" = ?
                ORDER BY l."LEADID" DESC';
            $leads = DB::select(
                $dealerInquiriesSql,
                [$dealerId]
            );

            if ($focusLeadId > 0) {
                $hasFocusLead = false;
                foreach ($leads as $leadRow) {
                    if ((int) ($leadRow->LEADID ?? 0) === $focusLeadId) {
                        $hasFocusLead = true;
                        break;
                    }
                }

                if (!$hasFocusLead) {
                    $focusLeadRows = DB::select(
                        'SELECT FIRST 1
                            l."LEADID", l."PRODUCTID", l."COMPANYNAME", l."CONTACTNAME", l."CONTACTNO", l."EMAIL",
                            l."ADDRESS1", l."ADDRESS2", l."CITY", l."POSTCODE", l."BUSINESSNATURE", l."USERCOUNT",
                            l."EXISTINGSOFTWARE", l."DEMOMODE", l."DESCRIPTION", l."REFERRALCODE", l."CREATEDAT", l."CREATEDBY",
                            l."ASSIGNED_TO", l."LASTMODIFIED",
                            u."EMAIL" AS "ASSIGNED_BY_EMAIL",
                            COALESCE(
                                (SELECT FIRST 1 la."STATUS"
                                   FROM "LEAD_ACT" la
                                  WHERE la."LEADID" = l."LEADID"
                                  ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                                l."CURRENTSTATUS",
                                \'Pending\'
                            ) AS "ACT_STATUS"
                        FROM "LEAD" l
                        LEFT JOIN "USERS" u ON u."USERID" = l."CREATEDBY"
                        WHERE l."ASSIGNED_TO" = ?
                          AND l."LEADID" = ?
                        ORDER BY l."LEADID" DESC',
                        [$dealerId, $focusLeadId]
                    );

                    if (!empty($focusLeadRows)) {
                        $focusLeadRow = $focusLeadRows[0];
                        $focusLeadValue = (int) ($focusLeadRow->LEADID ?? 0);
                        $inserted = false;
                        $sortedLeads = [];

                        foreach ($leads as $leadRow) {
                            $leadValue = (int) ($leadRow->LEADID ?? 0);
                            if (!$inserted && $focusLeadValue > $leadValue) {
                                $sortedLeads[] = $focusLeadRow;
                                $inserted = true;
                            }
                            $sortedLeads[] = $leadRow;
                        }

                        if (!$inserted) {
                            $sortedLeads[] = $focusLeadRow;
                        }

                        $leads = $sortedLeads;
                    }
                }
            }

            // Assigned By: same logic as admin assigned inquiries — SYSTEMROLE-ALIAS (e.g. Admin-Wei Jian)
            try {
                $ids = [];
                foreach ($leads as $r) {
                    $by = trim((string) ($r->CREATEDBY ?? ''));
                    if ($by !== '') {
                        $ids[$by] = true;
                    }
                }
                $ids = array_keys($ids);
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $users = DB::select(
                        'SELECT "USERID","SYSTEMROLE","ALIAS","COMPANY","EMAIL" FROM "USERS" WHERE CAST("USERID" AS VARCHAR(50)) IN (' . $placeholders . ')',
                        $ids
                    );
                    $createdByMap = [];
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
                            $createdByMap[$uid] = $role . '- ' . $alias;
                        } elseif ($role !== '') {
                            $createdByMap[$uid] = $role . '- ' . ($company !== '' ? $company : ($email !== '' ? $email : $uid));
                        } elseif ($alias !== '') {
                            $createdByMap[$uid] = $alias;
                        } else {
                            $createdByMap[$uid] = $fallback;
                        }
                    }
                    foreach ($leads as $r) {
                        $by = trim((string) ($r->CREATEDBY ?? ''));
                        if ($by !== '' && isset($createdByMap[$by])) {
                            $r->CREATEDBY_NAME = $createdByMap[$by];
                        }
                    }
                }
            } catch (\Throwable $e) {
                // leave ASSIGNED_BY_EMAIL / no CREATEDBY_NAME
            }

            try {
                $leadIds = array_values(array_unique(array_filter(array_map(
                    fn ($r) => (int) ($r->LEADID ?? 0),
                    $leads
                ))));
                if (!empty($leadIds)) {
                    $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
                    $completedRows = DB::select(
                        'SELECT a."LEADID", a."CREATIONDATE" AS "COMPLETED_AT"
                         FROM "LEAD_ACT" a
                         JOIN (
                             SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                             FROM "LEAD_ACT"
                             WHERE UPPER(TRIM("STATUS")) = \'COMPLETED\'
                               AND "LEADID" IN (' . $placeholders . ')
                             GROUP BY "LEADID"
                         ) m ON m."LEADID" = a."LEADID" AND m.MAXCD = a."CREATIONDATE"
                         WHERE UPPER(TRIM(a."STATUS")) = \'COMPLETED\'
                           AND a."LEADID" IN (' . $placeholders . ')',
                        array_merge($leadIds, $leadIds)
                    );
                    $rewardedRows = DB::select(
                        'SELECT a."LEADID", a."CREATIONDATE" AS "REWARDED_AT"
                         FROM "LEAD_ACT" a
                         JOIN (
                             SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                             FROM "LEAD_ACT"
                             WHERE UPPER(TRIM("STATUS")) IN (\'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\')
                               AND "LEADID" IN (' . $placeholders . ')
                             GROUP BY "LEADID"
                         ) m ON m."LEADID" = a."LEADID" AND m.MAXCD = a."CREATIONDATE"
                        WHERE UPPER(TRIM(a."STATUS")) IN (\'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\')
                           AND a."LEADID" IN (' . $placeholders . ')',
                        array_merge($leadIds, $leadIds)
                    );
                    $assignRows = DB::select(
                        'SELECT a."LEADID", a."CREATIONDATE" AS "ASSIGNDATE"
                         FROM "LEAD_ACT" a
                         JOIN (
                             SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                             FROM "LEAD_ACT"
                             WHERE "LEADID" IN (' . $placeholders . ')
                               AND (
                                   UPPER(TRIM(COALESCE("SUBJECT", \'\'))) STARTING WITH \'LEAD ASSIGNED\'
                                   OR UPPER(TRIM(COALESCE("DESCRIPTION", \'\'))) STARTING WITH \'LEAD ASSIGNED\'
                               )
                             GROUP BY "LEADID"
                         ) m ON m."LEADID" = a."LEADID" AND m.MAXCD = a."CREATIONDATE"
                         WHERE a."LEADID" IN (' . $placeholders . ')
                           AND (
                               UPPER(TRIM(COALESCE(a."SUBJECT", \'\'))) STARTING WITH \'LEAD ASSIGNED\'
                               OR UPPER(TRIM(COALESCE(a."DESCRIPTION", \'\'))) STARTING WITH \'LEAD ASSIGNED\'
                           )',
                        array_merge($leadIds, $leadIds)
                    );
                    $completedDateMap = [];
                    foreach ($completedRows as $cr) {
                        $lid = (int) ($cr->LEADID ?? 0);
                        if ($lid > 0) {
                            $completedDateMap[$lid] = $cr->COMPLETED_AT ?? null;
                        }
                    }
                    $rewardedDateMap = [];
                    foreach ($rewardedRows as $rr) {
                        $lid = (int) ($rr->LEADID ?? 0);
                        if ($lid > 0) {
                            $rewardedDateMap[$lid] = $rr->REWARDED_AT ?? null;
                        }
                    }
                    $assignDateMap = [];
                    foreach ($assignRows as $ar) {
                        $lid = (int) ($ar->LEADID ?? 0);
                        if ($lid > 0) {
                            $assignDateMap[$lid] = $ar->ASSIGNDATE ?? null;
                        }
                    }
                    $attachRows = DB::select(
                        'SELECT a."LEADID", a."LEAD_ACTID", a."ATTACHMENT"
                         FROM "LEAD_ACT" a
                         JOIN (
                             SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                             FROM "LEAD_ACT"
                             WHERE "LEADID" IN (' . $placeholders . ')
                             GROUP BY "LEADID"
                         ) m ON m."LEADID" = a."LEADID" AND m.MAXCD = a."CREATIONDATE"
                         WHERE a."LEADID" IN (' . $placeholders . ')',
                        array_merge($leadIds, $leadIds)
                    );
                    $attachmentMap = [];
                    $attachmentActMap = [];
                    foreach ($attachRows as $ar) {
                        $lid = (int) ($ar->LEADID ?? 0);
                        if ($lid > 0) {
                            $attachmentMap[$lid] = $ar->ATTACHMENT ?? $ar->attachment ?? null;
                            $attachmentActMap[$lid] = (int) ($ar->LEAD_ACTID ?? 0);
                        }
                    }
                    foreach ($leads as $r) {
                        $lid = (int) ($r->LEADID ?? 0);
                        if ($lid > 0) {
                            if (isset($completedDateMap[$lid])) {
                                $r->COMPLETED_AT = $completedDateMap[$lid];
                            }
                            if (isset($rewardedDateMap[$lid])) {
                                $r->REWARDED_AT = $rewardedDateMap[$lid];
                            }
                            if (isset($assignDateMap[$lid])) {
                                $r->ASSIGNDATE = $assignDateMap[$lid];
                            }
                            $r->ATTACHMENT_URLS = $this->buildInquiryActivityAttachmentUrls(
                                $attachmentMap[$lid] ?? null,
                                $lid,
                                $attachmentActMap[$lid] ?? 0,
                                'dealer.inquiries.serve-attachment',
                                'dealer.inquiries.activity-attachment'
                            );
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore attachment mapping failures
            }
        }
        return view('dealer.inquiries', [
            'leads' => $leads,
            'focusLeadId' => $focusLeadId,
            'dealerInquiryCount' => $dealerConsoleCounts['inquiries'],
            'dealerPendingPayoutCount' => $dealerConsoleCounts['pending_payouts'],
            'dealerConsoleTab' => 'inquiries',
            'currentPage' => 'inquiries',
        ]);
    }

    public function inquiriesSync(Request $request): JsonResponse
    {
        // Reuse the same data-loading logic as the main dealer inquiries page
        $view = $this->inquiries($request);
        $data = $view->getData();

        // Ensure product name labels are available to the partial so it can
        // render friendly product names instead of "Product 1/2/3" fallbacks.
        if (!isset($data['productNames'])) {
            $data['productNames'] = [
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
        }

        $rowsHtml = view('dealer.partials.inquiries_rows', $data)->render();

        return response()->json([
            'rows' => $rowsHtml,
        ]);
    }

    public function payoutsSync(Request $request): JsonResponse
    {
        // Reuse the same data-loading logic as the main dealer payouts page
        $view = $this->payouts($request);
        $data = $view->getData();

        // Ensure product name labels are available to the partial (used for dealt products pills).
        if (!isset($data['productNames'])) {
            $data['productNames'] = [
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
        }

        $completedRowsHtml = view('dealer.partials.payouts_completed_rows', $data)->render();
        $rewardedRowsHtml = view('dealer.partials.payouts_rewarded_rows', $data)->render();

        return response()->json([
            'completed_rows' => $completedRowsHtml,
            'rewarded_rows' => $rewardedRowsHtml,
        ]);
    }

    public function inquiryActivity(Request $request, int $leadId): JsonResponse
    {
        $dealerId = $request->session()->get('user_id');
        if (!$dealerId) {
            return response()->json(['activities' => []], 200);
        }

        $lead = DB::selectOne(
            'SELECT "LEADID", "REFERRALCODE" FROM "LEAD" WHERE "LEADID" = ? AND "ASSIGNED_TO" = ?',
            [$leadId, $dealerId]
        );
        if (!$lead) {
            return response()->json(['activities' => []], 200);
        }

        $activities = [];
        $lastProductIds = [];
        $rows = DB::select(
            'SELECT la."LEAD_ACTID", la."USERID", la."CREATIONDATE", la."SUBJECT", la."DESCRIPTION", la."STATUS", la."ATTACHMENT", la."DEALTPRODUCT", u."EMAIL" AS "USER_EMAIL"
             FROM "LEAD_ACT" la
             LEFT JOIN "USERS" u ON u."USERID" = la."USERID"
             WHERE la."LEADID" = ?
             ORDER BY la."CREATIONDATE" ASC, la."LEAD_ACTID" ASC',
            [$leadId]
        );

        // Collect user IDs that appear either as activity USERID or inside
        // assignment descriptions like "Lead Assigned by U001 to U003"
        $assignUserIds = [];
        foreach ($rows as $r) {
            $desc = trim((string) ($r->DESCRIPTION ?? ''));
            if ($desc !== '' && stripos($desc, 'Lead Assigned by') === 0) {
                if (preg_match('/Lead Assigned by\s+(\S+)\s+to\s+(\S+)/i', $desc, $m)) {
                    $fromId = trim($m[1] ?? '');
                    $toId = trim($m[2] ?? '');
                    if ($fromId !== '') {
                        $assignUserIds[$fromId] = true;
                    }
                    if ($toId !== '') {
                        $assignUserIds[$toId] = true;
                    }
                }
            }
        }

        // Resolve human-friendly names for activity user (same style as admin: SYSTEMROLE-ALIAS)
        $userNameMap = [];
        try {
            $ids = [];
            foreach ($rows as $r) {
                $uid = trim((string) ($r->USERID ?? ''));
                if ($uid !== '') {
                    $ids[$uid] = true;
                }
            }
            foreach (array_keys($assignUserIds) as $aid) {
                $ids[$aid] = true;
            }
            $ids = array_keys($ids);
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

        foreach ($rows as $r) {
            $status = trim($r->STATUS ?? '');
            if (strtoupper($status) === 'CREATED') {
                continue;
            }

            // Normalize activity timestamp to an ISO‑8601 string in the app's timezone
            $createdAtIso = null;
            if (!empty($r->CREATIONDATE)) {
                try {
                    $createdAtIso = Carbon::parse($r->CREATIONDATE)->toIso8601String();
                } catch (\Throwable $e) {
                    $createdAtIso = (string) $r->CREATIONDATE;
                }
            }

            $attachmentUrls = [];
            $attachmentRaw = $r->ATTACHMENT ?? $r->attachment ?? null;
            if ($attachmentRaw !== null && trim((string) $attachmentRaw) !== '') {
                $attachmentStr = trim((string) $attachmentRaw);
                $attachmentStr = str_replace('\\', '/', $attachmentStr);
                if (str_contains($attachmentStr, ',') || str_starts_with($attachmentStr, 'inquiry-attachments')) {
                    foreach (explode(',', $attachmentStr) as $path) {
                        $path = trim(str_replace('\\', '/', $path));
                        if ($path !== '' && str_starts_with($path, 'inquiry-attachments/')) {
                            $attachmentUrls[] = route('dealer.inquiries.serve-attachment', ['path' => $path]);
                        }
                    }
                } else {
                    if (str_starts_with($attachmentStr, 'inquiry-attachments/')) {
                        $attachmentUrls[] = route('dealer.inquiries.serve-attachment', ['path' => $attachmentStr]);
                    } elseif (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $attachmentStr)) {
                        $attachmentUrls[] = route('dealer.inquiries.activity-attachment', ['leadId' => $leadId, 'leadActId' => (int) ($r->LEAD_ACTID ?? 0)]);
                    }
                }
            }

            $productIds = [];
            // Parse DEALTPRODUCT (e.g. "1, 2, 3") into numeric product IDs for the UI
            $dealtRaw = $r->DEALTPRODUCT ?? null;
            if ($dealtRaw !== null && trim((string) $dealtRaw) !== '') {
                $tokens = preg_split('/[,\s\(\)]+/', (string) $dealtRaw);
                foreach ($tokens as $tok) {
                    if ($tok === '') {
                        continue;
                    }
                    $pid = (int) $tok;
                    if ($pid >= 1 && $pid <= 11) {
                        $productIds[] = $pid;
                    }
                }
                $productIds = array_values(array_unique($productIds));
            }

            // Carry forward last non-empty product selection so Rewarded steps
            // inherit the same products as the preceding Completed step.
            if (!empty($productIds)) {
                $lastProductIds = $productIds;
            } elseif (empty($productIds) && !empty($lastProductIds) &&
                in_array(strtoupper($status), ['COMPLETED', 'REWARDED', 'REWARD DISTRIBUTED'], true)
            ) {
                $productIds = $lastProductIds;
            }

            $userDisplay = $r->USERID ? ($userNameMap[trim($r->USERID)] ?? $r->USERID) : 'System';
            $description = trim($r->DESCRIPTION ?? '');

            $activities[] = [
                'type' => 'activity',
                'user' => $userDisplay,
                'user_id' => trim((string) ($r->USERID ?? '')),
                'subject' => trim($r->SUBJECT ?? ''),
                'description' => $description,
                'status' => $status,
                'created_at' => $createdAtIso,
                'attachment_urls' => $attachmentUrls,
                'product_ids' => $productIds,
            ];
        }

        usort($activities, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        // Latest status by latest CREATIONDATE (and LEAD_ACTID tie-breaker)
        $latestRow = DB::selectOne(
            'SELECT FIRST 1 la."STATUS", la."CREATIONDATE"
               FROM "LEAD_ACT" la
              WHERE la."LEADID" = ?
                AND UPPER(TRIM(COALESCE(la."STATUS", \'\'))) <> \'CREATED\'
              ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC',
            [$leadId]
        );
        $latestStatus = $latestRow ? trim((string) ($latestRow->STATUS ?? '')) : '';
        $latestCreatedAt = null;
        if ($latestRow && !empty($latestRow->CREATIONDATE)) {
            try {
                $latestCreatedAt = Carbon::parse($latestRow->CREATIONDATE)->toIso8601String();
            } catch (\Throwable $e) {
                $latestCreatedAt = (string) $latestRow->CREATIONDATE;
            }
        }

        $latestNonFailedRow = DB::selectOne(
            'SELECT FIRST 1 la."STATUS"
               FROM "LEAD_ACT" la
              WHERE la."LEADID" = ?
                AND UPPER(TRIM(COALESCE(la."STATUS", \'\'))) NOT IN (\'CREATED\', \'FAILED\')
              ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC',
            [$leadId]
        );
        $latestNonFailedStatus = $latestNonFailedRow ? trim((string) ($latestNonFailedRow->STATUS ?? '')) : '';

        $lastReward = null;
        $lastRow = DB::selectOne(
            'SELECT FIRST 1 la."CREATIONDATE", la."DESCRIPTION" FROM "LEAD_ACT" la
             WHERE la."LEADID" = ? AND UPPER(TRIM(COALESCE(la."STATUS", \'\'))) IN (\'REWARDED\', \'REWARD DISTRIBUTED\')
             ORDER BY la."CREATIONDATE" DESC',
            [$leadId]
        );
        if ($lastRow && $lastRow->CREATIONDATE) {
            try {
                $created = Carbon::parse($lastRow->CREATIONDATE);
            } catch (\Throwable $e) {
                $created = Carbon::createFromTimestamp(strtotime($lastRow->CREATIONDATE));
            }

            $lastReward = [
                'created_at' => $created->toIso8601String(),
                'date' => $created->format('Y-m-d'),
                'time' => $created->format('H:i'),
                'description' => trim($lastRow->DESCRIPTION ?? ''),
            ];
        }

        return response()->json([
            'activities' => $activities,
            'last_reward_details' => $lastReward,
            'latest_status' => $latestStatus,
            'latest_non_failed_status' => $latestNonFailedStatus,
            'latest_created_at' => $latestCreatedAt,
        ]);
    }

    /**
     * Serve an attachment image by path (from storage). Used so images display without requiring storage:link.
     */
    public function serveInquiryAttachment(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $dealerId = $request->session()->get('user_id');
        if (!$dealerId) {
            return response('', 404);
        }
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
        return response()->file($fullPath, ['Content-Type' => $mime]);
    }

    /**
     * Serve a single activity attachment (image). Supports path-based storage or binary BLOB in DB.
     */
    public function inquiryActivityAttachment(Request $request, int $leadId, int $leadActId): \Symfony\Component\HttpFoundation\Response
    {
        $dealerId = $request->session()->get('user_id');
        if (!$dealerId) {
            return response('', 404);
        }
        $lead = DB::selectOne(
            'SELECT "LEADID", "REFERRALCODE" FROM "LEAD" WHERE "LEADID" = ? AND "ASSIGNED_TO" = ?',
            [$leadId, $dealerId]
        );
        if (!$lead) {
            return response('', 404);
        }
        $row = DB::selectOne('SELECT "ATTACHMENT" FROM "LEAD_ACT" WHERE "LEAD_ACTID" = ? AND "LEADID" = ?', [$leadActId, $leadId]);
        $attachment = $row->ATTACHMENT ?? $row->attachment ?? null;
        if (!$row || $attachment === null || trim((string) $attachment) === '') {
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

    public function updateInquiryStatus(Request $request): JsonResponse
    {
        $isMultipart = $request->hasFile('attachments');
        if ($isMultipart) {
            $request->validate([
                'lead_id' => 'required|integer',
                'status' => 'required|string|max:50',
                'remark' => 'nullable|string|max:4000',
                'attachments' => 'nullable|array',
                'attachments.*' => 'image|max:5120',
            ]);
            $products = [];
            $productsJson = $request->input('products');
            if (is_string($productsJson)) {
                $decoded = json_decode($productsJson, true);
                $products = is_array($decoded) ? $decoded : [];
            }
        } else {
            $validated = $request->validate([
                'lead_id' => 'required|integer',
                'status' => 'required|string|max:50',
                'remark' => 'nullable|string|max:4000',
                'products' => 'nullable|array',
                'products.*.id' => 'nullable',
                'products.*.name' => 'nullable|string|max:100',
            ]);
            $products = $validated['products'] ?? [];
        }

        $leadId = (int) $request->input('lead_id');
        $status = trim((string) $request->input('status'));
        $remark = trim((string) ($request->input('remark') ?? ''));
        $activityDate = trim((string) ($request->input('activity_date') ?? ''));
        $activityTime = trim((string) ($request->input('activity_time') ?? ''));

        $creationDate = now()->format('Y-m-d H:i:s');
        if ($activityDate !== '' && $activityTime !== '') {
            try {
                $parsed = Carbon::parse($activityDate . ' ' . $activityTime);
                $creationDate = $parsed->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                // keep default
            }
        }

        $dealerId = $request->session()->get('user_id');
        if (!$dealerId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $lead = DB::selectOne(
            'SELECT "LEADID", "REFERRALCODE" FROM "LEAD" WHERE "LEADID" = ? AND "ASSIGNED_TO" = ?',
            [$leadId, $dealerId]
        );
        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found or not assigned to you'], 404);
        }
        if (strtoupper($this->mapStatusToDb($status)) === 'COMPLETED' && empty($products)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one product for COMPLETED status.',
            ], 422);
        }
        $statusDb = $this->mapStatusToDb($status);
        if (strtoupper($statusDb) === 'REWARDED' && !$request->hasFile('attachments')) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload at least one attachment for REWARDED status.',
            ], 422);
        }

        $lastAct = DB::selectOne(
            'SELECT FIRST 1 la."STATUS", la."CREATIONDATE"
               FROM "LEAD_ACT" la
              WHERE la."LEADID" = ?
              ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC',
            [$leadId]
        );
        $fromStatus = $lastAct ? trim($lastAct->STATUS ?? '') : 'Pending';
        $lastCreationDate = $lastAct ? ($lastAct->CREATIONDATE ?? null) : null;

        // Enforce chronological order: user cannot set a status datetime earlier than the latest saved status.
        if ($lastCreationDate) {
            try {
                $lastDt = Carbon::parse($lastCreationDate);
                $newDt = Carbon::parse($creationDate);
                if ($newDt->lt($lastDt)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid date/time. It must be on/after the previous status time (' . $lastDt->format('Y-m-d H:i') . ').',
                    ], 422);
                }
            } catch (\Throwable $e) {
                // If parsing fails, skip this validation.
            }
        }

        $normalizeTransitionStatus = static function (string $value): string {
            $upper = strtoupper(trim($value));
            return match ($upper) {
                'FOLLOWUP' => 'FOLLOW UP',
                'CASE CONFIRMED' => 'CONFIRMED',
                'CASE COMPLETED' => 'COMPLETED',
                'REWARD DISTRIBUTED' => 'REWARDED',
                default => $upper,
            };
        };
        $formatTransitionLabel = static function (string $value): string {
            return match ($value) {
                'FOLLOW UP' => 'Follow Up',
                'PENDING' => 'Pending',
                'DEMO' => 'Demo',
                'CONFIRMED' => 'Confirmed',
                'COMPLETED' => 'Completed',
                'REWARDED' => 'Rewarded',
                default => ucwords(strtolower(str_replace('_', ' ', $value))),
            };
        };

        $fromUpper = $normalizeTransitionStatus($fromStatus);
        $toUpper = $normalizeTransitionStatus($statusDb);
        $hasReferralCode = trim((string) ($lead->REFERRALCODE ?? '')) !== '';

        // If dealer is "editing" the current status (same status again), allow it.
        $isSameStatusEdit = $toUpper === $fromUpper;

        if (!$isSameStatusEdit) {
            $allowedTo = match ($fromUpper) {
                'PENDING' => ['FOLLOW UP'],
                'FOLLOW UP' => ['DEMO', 'CONFIRMED', 'COMPLETED'],
                'DEMO' => ['CONFIRMED', 'COMPLETED'],
                'CONFIRMED' => ['COMPLETED'],
                'COMPLETED' => $hasReferralCode ? ['REWARDED'] : [],
                default => [],
            };

            if (!in_array($toUpper, $allowedTo, true)) {
                if ($fromUpper === 'PENDING') {
                    $message = 'You cant change status from Pending To ' . $formatTransitionLabel($toUpper) . ', Please Follow Up First';
                } elseif ($toUpper === 'REWARDED' && !$hasReferralCode) {
                    $message = 'You cant change status to Rewarded, Referral Code is required first';
                } elseif ($toUpper === 'REWARDED') {
                    $message = 'You cant change status to Rewarded, Please Complete First';
                } else {
                    $message = 'You cant change status from ' . $formatTransitionLabel($fromUpper) . ' To ' . $formatTransitionLabel($toUpper);
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }
        }

        $toStatus = $statusDb;
        $description = $remark !== '' ? $remark : ('Update Status from ' . $fromStatus . ' to ' . $toStatus);
        if (! empty($products)) {
            $productNames = array_map(fn ($p) => $p['name'] ?? 'Product ' . ($p['id'] ?? ''), $products);
            $description = 'Products: ' . implode(', ', $productNames) . "\n\n" . $description;
        }

        $attachmentValue = null;
        if ($request->hasFile('attachments')) {
            $paths = [];
            $dir = 'inquiry-attachments/lead_' . $leadId;
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid() && str_starts_with($file->getMimeType(), 'image/')) {
                    $path = $file->store($dir, 'public');
                    if ($path) {
                        $paths[] = $path;
                    }
                }
            }
            if (! empty($paths)) {
                $attachmentValue = implode(',', $paths);
            }
        }

        $isCompleted = strtoupper($statusDb) === 'COMPLETED' && ! empty($products);
        if ($isCompleted) {
            $productIds = [];
            foreach ($products as $p) {
                $pid = (int) ($p['id'] ?? 0);
                if ($pid >= 1 && $pid <= 11) {
                    $productIds[] = $pid;
                }
            }
            $dealtProduct = ! empty($productIds) ? implode(', ', $productIds) : null;
            DB::insert(
                'INSERT INTO "LEAD_ACT" ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS","DEALTPRODUCT")
                 VALUES (GEN_ID("GEN_LEAD_ACTID", 1),?,?,?,?,?,?,?,?)',
                [$leadId, $dealerId, $creationDate, 'Updated Status', $description, $attachmentValue, $statusDb, $dealtProduct]
            );
        } else {
            DB::insert(
                'INSERT INTO "LEAD_ACT" ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS")
                 VALUES (GEN_ID("GEN_LEAD_ACTID", 1),?,?,?,?,?,?,?)',
                [$leadId, $dealerId, $creationDate, 'Updated Status', $description, $attachmentValue, $statusDb]
            );
        }

        DB::update('UPDATE "LEAD" SET "LASTMODIFIED" = CURRENT_TIMESTAMP WHERE "LEADID" = ?', [$leadId]);

        return response()->json(['success' => true, 'message' => 'Status updated']);
    }

    private function mapStatusToDb(string $status): string
    {
        $map = [
            'PENDING' => 'Pending',
            'FOLLOW UP' => 'FollowUp',
            'DEMO' => 'Demo',
            'CONFIRMED' => 'Confirmed',
            'COMPLETED' => 'Completed',
            'REWARDED' => 'Rewarded',
            'REWARD DISTRIBUTED' => 'Rewarded',
            'FAILED' => 'Failed',
        ];
        $upper = strtoupper(trim($status));
        return $map[$upper] ?? $status;
    }

    public function payouts(Request $request): View
    {
        $dealerId = $request->session()->get('user_id');
        $dealerConsoleCounts = $this->getDealerConsoleCounts($dealerId);
        $rows = [];
        $completed = [];
        $rewarded = [];
        $totalCompletedLeads = $dealerConsoleCounts['pending_payouts'];

        if ($dealerId) {
            // Base LEAD data (dealer-only)
            $rows = DB::select(
                'SELECT FIRST 200
                    "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL",
                    "ADDRESS1","ADDRESS2","CITY","POSTCODE","BUSINESSNATURE","USERCOUNT",
                    "EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                    "CURRENTSTATUS","CREATEDAT","CREATEDBY","ASSIGNED_TO","LASTMODIFIED"
                 FROM "LEAD"
                 WHERE "ASSIGNED_TO" = ?
                 ORDER BY "LEADID" DESC',
                [$dealerId]
            );

            // Override CURRENTSTATUS from latest LEAD_ACT per LEADID (same approach as admin rewards)
            try {
                $leadIds = array_values(array_unique(array_filter(array_map(
                    fn ($r) => (int) ($r->LEADID ?? 0),
                    $rows
                ))));
                if (!empty($leadIds)) {
                    $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
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
                        $lid = (int) ($a->LEADID ?? 0);
                        if ($lid > 0) {
                            $statusMap[$lid] = trim((string) ($a->STATUS ?? ''));
                        }
                    }
                    if (!empty($statusMap)) {
                        foreach ($rows as $r) {
                            $lid = (int) ($r->LEADID ?? 0);
                            if ($lid > 0 && isset($statusMap[$lid])) {
                                $r->CURRENTSTATUS = $statusMap[$lid];
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // keep CURRENTSTATUS from LEAD if override fails
            }

            // Attach latest COMPLETED dealt products per lead (for "Dealt Products" column)
            try {
                if (!empty($leadIds)) {
                    $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
                    $dealRows = DB::select(
                        'SELECT a."LEADID", a."DEALTPRODUCT"
                         FROM "LEAD_ACT" a
                         JOIN (
                             SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                             FROM "LEAD_ACT"
                             WHERE UPPER(TRIM("STATUS")) = \'COMPLETED\' AND "LEADID" IN (' . $placeholders . ')
                             GROUP BY "LEADID"
                         ) m ON m."LEADID" = a."LEADID" AND m.MAXCD = a."CREATIONDATE"
                         WHERE UPPER(TRIM(a."STATUS")) = \'COMPLETED\' AND a."LEADID" IN (' . $placeholders . ')',
                        array_merge($leadIds, $leadIds)
                    );
                    $dealtMap = [];
                    foreach ($dealRows as $dr) {
                        $lid = (int) ($dr->LEADID ?? 0);
                        if ($lid > 0) {
                            $dealtMap[$lid] = $dr->DEALTPRODUCT ?? $dr->dealtproduct ?? null;
                        }
                    }
                    if (!empty($dealtMap)) {
                        foreach ($rows as $r) {
                            $lid = (int) ($r->LEADID ?? 0);
                            if ($lid > 0 && isset($dealtMap[$lid])) {
                                $r->DEALTPRODUCT = $dealtMap[$lid];
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore dealt product mapping failures
            }

            // Attach latest assignment date per lead for optional "Assign Date" column
            try {
                if (!empty($leadIds)) {
                    $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
                    $assignRows = DB::select(
                        'SELECT a."LEADID", a."CREATIONDATE" AS "ASSIGNDATE"
                         FROM "LEAD_ACT" a
                         JOIN (
                             SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                             FROM "LEAD_ACT"
                             WHERE "LEADID" IN (' . $placeholders . ')
                               AND (
                                   UPPER(TRIM(COALESCE("SUBJECT", \'\'))) STARTING WITH \'LEAD ASSIGNED\'
                                   OR UPPER(TRIM(COALESCE("DESCRIPTION", \'\'))) STARTING WITH \'LEAD ASSIGNED\'
                               )
                             GROUP BY "LEADID"
                         ) m ON m."LEADID" = a."LEADID" AND m.MAXCD = a."CREATIONDATE"
                         WHERE a."LEADID" IN (' . $placeholders . ')
                           AND (
                               UPPER(TRIM(COALESCE(a."SUBJECT", \'\'))) STARTING WITH \'LEAD ASSIGNED\'
                               OR UPPER(TRIM(COALESCE(a."DESCRIPTION", \'\'))) STARTING WITH \'LEAD ASSIGNED\'
                           )',
                        array_merge($leadIds, $leadIds)
                    );
                    $assignDateMap = [];
                    foreach ($assignRows as $ar) {
                        $lid = (int) ($ar->LEADID ?? 0);
                        if ($lid > 0) {
                            $assignDateMap[$lid] = $ar->ASSIGNDATE ?? null;
                        }
                    }
                    if (!empty($assignDateMap)) {
                        foreach ($rows as $r) {
                            $lid = (int) ($r->LEADID ?? 0);
                            if ($lid > 0 && isset($assignDateMap[$lid])) {
                                $r->ASSIGNDATE = $assignDateMap[$lid];
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore assign date mapping failures
            }

            foreach ($rows as $r) {
                $status = strtoupper(trim((string) ($r->CURRENTSTATUS ?? '')));
                $referral = trim((string) ($r->REFERRALCODE ?? ''));
                if ($status === 'COMPLETED') {
                    if ($referral !== '') {
                        $completed[] = $r;
                    }
                } elseif (in_array($status, ['REWARDED', 'PAID'], true)) {
                    $rewarded[] = $r;
                }
            }

            // Attach latest COMPLETED attachment per lead for completed list
            try {
                $completedIds = array_values(array_unique(array_filter(array_map(
                    fn ($r) => (int) ($r->LEADID ?? 0),
                    $completed
                ))));
                if (!empty($completedIds)) {
                    $placeholders = implode(',', array_fill(0, count($completedIds), '?'));
                    $attachRows = DB::select(
                        'SELECT a."LEADID", a."LEAD_ACTID", a."ATTACHMENT"
                         FROM "LEAD_ACT" a
                         JOIN (
                             SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                             FROM "LEAD_ACT"
                             WHERE UPPER(TRIM("STATUS")) = \'COMPLETED\'
                               AND "LEADID" IN (' . $placeholders . ')
                             GROUP BY "LEADID"
                         ) m ON m."LEADID" = a."LEADID" AND m.MAXCD = a."CREATIONDATE"
                         WHERE UPPER(TRIM(a."STATUS")) = \'COMPLETED\'
                           AND a."LEADID" IN (' . $placeholders . ')',
                        array_merge($completedIds, $completedIds)
                    );
                    $attachmentMap = [];
                    $attachmentActMap = [];
                    foreach ($attachRows as $ar) {
                        $lid = (int) ($ar->LEADID ?? 0);
                        if ($lid > 0) {
                            $attachmentMap[$lid] = $ar->ATTACHMENT ?? $ar->attachment ?? null;
                            $attachmentActMap[$lid] = (int) ($ar->LEAD_ACTID ?? 0);
                        }
                    }
                    if (!empty($attachmentMap)) {
                        foreach ($completed as $r) {
                            $lid = (int) ($r->LEADID ?? 0);
                            if ($lid > 0 && array_key_exists($lid, $attachmentMap)) {
                                $r->COMPLETED_ATTACHMENT = $attachmentMap[$lid];
                                $r->COMPLETED_LEAD_ACT_ID = $attachmentActMap[$lid] ?? 0;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore attachment mapping failures
            }

            // Attach latest REWARDED/PAID attachment per lead for rewarded list
            try {
                $rewardedIds = array_values(array_unique(array_filter(array_map(
                    fn ($r) => (int) ($r->LEADID ?? 0),
                    $rewarded
                ))));
                if (!empty($rewardedIds)) {
                    $placeholders = implode(',', array_fill(0, count($rewardedIds), '?'));
                    $completedRows = DB::select(
                        'SELECT a."LEADID", a."CREATIONDATE"
                         FROM "LEAD_ACT" a
                         JOIN (
                             SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                             FROM "LEAD_ACT"
                             WHERE UPPER(TRIM("STATUS")) = \'COMPLETED\'
                               AND "LEADID" IN (' . $placeholders . ')
                             GROUP BY "LEADID"
                         ) m ON m."LEADID" = a."LEADID" AND m.MAXCD = a."CREATIONDATE"
                         WHERE UPPER(TRIM(a."STATUS")) = \'COMPLETED\'
                           AND a."LEADID" IN (' . $placeholders . ')',
                        array_merge($rewardedIds, $rewardedIds)
                    );
                    $completedDateMap = [];
                    foreach ($completedRows as $cr) {
                        $lid = (int) ($cr->LEADID ?? 0);
                        if ($lid > 0) {
                            $completedDateMap[$lid] = $cr->CREATIONDATE ?? null;
                        }
                    }
                    $attachRows = DB::select(
                        'SELECT a."LEADID", a."LEAD_ACTID", a."ATTACHMENT", a."CREATIONDATE"
                         FROM "LEAD_ACT" a
                         JOIN (
                             SELECT "LEADID", MAX("CREATIONDATE") AS MAXCD
                             FROM "LEAD_ACT"
                             WHERE UPPER(TRIM("STATUS")) IN (\'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\')
                               AND "LEADID" IN (' . $placeholders . ')
                             GROUP BY "LEADID"
                         ) m ON m."LEADID" = a."LEADID" AND m.MAXCD = a."CREATIONDATE"
                         WHERE UPPER(TRIM(a."STATUS")) IN (\'REWARDED\', \'PAID\', \'REWARD DISTRIBUTED\')
                           AND a."LEADID" IN (' . $placeholders . ')',
                        array_merge($rewardedIds, $rewardedIds)
                    );
                    $attachmentMap = [];
                    $attachmentActMap = [];
                    $rewardDateMap = [];
                    foreach ($attachRows as $ar) {
                        $lid = (int) ($ar->LEADID ?? 0);
                        if ($lid > 0) {
                            $attachmentMap[$lid] = $ar->ATTACHMENT ?? $ar->attachment ?? null;
                            $attachmentActMap[$lid] = (int) ($ar->LEAD_ACTID ?? 0);
                            $rewardDateMap[$lid] = $ar->CREATIONDATE ?? null;
                        }
                    }
                    foreach ($rewarded as $r) {
                        $lid = (int) ($r->LEADID ?? 0);
                        if ($lid <= 0) {
                            continue;
                        }
                        if (isset($completedDateMap[$lid])) {
                            $r->COMPLETED_AT = $completedDateMap[$lid];
                        }
                        if (array_key_exists($lid, $attachmentMap)) {
                            $r->REWARD_ATTACHMENT = $attachmentMap[$lid];
                            $r->REWARD_LEAD_ACT_ID = $attachmentActMap[$lid] ?? 0;
                            $r->REWARD_DATE = $rewardDateMap[$lid] ?? null;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore attachment mapping failures
            }

            // Build attachment URLs for Completed list (dealer)
            foreach ($completed as $r) {
                $r->COMPLETED_ATTACHMENT_URLS = $this->buildInquiryActivityAttachmentUrls(
                    $r->COMPLETED_ATTACHMENT ?? null,
                    (int) ($r->LEADID ?? 0),
                    (int) ($r->COMPLETED_LEAD_ACT_ID ?? 0),
                    'dealer.inquiries.serve-attachment',
                    'dealer.inquiries.activity-attachment'
                );
            }

            // Build attachment URLs for Rewarded list (dealer)
            foreach ($rewarded as $r) {
                $r->REWARD_ATTACHMENT_URLS = $this->buildInquiryActivityAttachmentUrls(
                    $r->REWARD_ATTACHMENT ?? null,
                    (int) ($r->LEADID ?? 0),
                    (int) ($r->REWARD_LEAD_ACT_ID ?? 0),
                    'dealer.inquiries.serve-attachment',
                    'dealer.inquiries.activity-attachment'
                );
            }

            // Resolve CREATEDBY_NAME and ASSIGNED_TO_NAME for display (same as admin rewards)
            try {
                $ids = [];
                foreach (array_merge($completed, $rewarded) as $r) {
                    $to = trim((string) ($r->ASSIGNED_TO ?? ''));
                    $by = trim((string) ($r->CREATEDBY ?? ''));
                    if ($to !== '') {
                        $ids[$to] = true;
                    }
                    if ($by !== '') {
                        $ids[$by] = true;
                    }
                }
                $ids = array_keys($ids);
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $users = DB::select(
                        'SELECT "USERID","SYSTEMROLE","ALIAS","COMPANY","EMAIL"
                         FROM "USERS"
                         WHERE CAST("USERID" AS VARCHAR(50)) IN (' . $placeholders . ')',
                        $ids
                    );
                    $assignedToMap = [];
                    $createdByMap = [];
                    foreach ($users as $u) {
                        $uid = trim((string) ($u->USERID ?? ''));
                        if ($uid === '') {
                            continue;
                        }
                        $role = trim((string) ($u->SYSTEMROLE ?? ''));
                        $company = trim((string) ($u->COMPANY ?? ''));
                        $alias = trim((string) ($u->ALIAS ?? ''));
                        $email = trim((string) ($u->EMAIL ?? ''));
                        $fallback = $email !== '' ? $email : $uid;

                        if ($company !== '' && $alias !== '') {
                            $assignedToMap[$uid] = $company . '- ' . $alias;
                        } elseif ($company !== '') {
                            $assignedToMap[$uid] = $company;
                        } elseif ($alias !== '') {
                            $assignedToMap[$uid] = $alias;
                        } else {
                            $assignedToMap[$uid] = $fallback;
                        }

                        if ($role !== '' && $alias !== '') {
                            $createdByMap[$uid] = $role . '- ' . $alias;
                        } elseif ($role !== '') {
                            $createdByMap[$uid] = $role . '-' . ($company !== '' ? $company : ($email !== '' ? $email : $uid));
                        } elseif ($alias !== '') {
                            $createdByMap[$uid] = $alias;
                        } else {
                            $createdByMap[$uid] = $fallback;
                        }
                    }

                    foreach (array_merge($completed, $rewarded) as $r) {
                        $to = trim((string) ($r->ASSIGNED_TO ?? ''));
                        $by = trim((string) ($r->CREATEDBY ?? ''));
                        if ($to !== '' && isset($assignedToMap[$to])) {
                            $r->ASSIGNED_TO_NAME = $assignedToMap[$to];
                        }
                        if ($by !== '' && isset($createdByMap[$by])) {
                            $r->CREATEDBY_NAME = $createdByMap[$by];
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore mapping failures
            }

        }

        $productLabels = [
            1 => 'Account',
            2 => 'Payroll',
            3 => 'Production',
            4 => 'Mobile Sales',
            5 => 'Ecommerce',
            6 => 'EBI POS',
            7 => 'Sudu AI',
            8 => 'X-Store',
            9 => 'Vision',
            10 => 'HRMS',
            11 => 'Others',
        ];

        return view('dealer.payouts', [
            'completed' => $completed,
            'rewarded' => $rewarded,
            'totalCompletedLeads' => $totalCompletedLeads,
            'totalRewardedLeads' => count($rewarded),
            'productLabels' => $productLabels,
            'dealerInquiryCount' => $dealerConsoleCounts['inquiries'],
            'dealerPendingPayoutCount' => $dealerConsoleCounts['pending_payouts'],
            'dealerConsoleTab' => 'pending-payouts',
            'currentPage' => 'inquiries',
        ]);
    }

    private function getDealerConsoleCounts($dealerId): array
    {
        $counts = [
            'inquiries' => 0,
            'pending_payouts' => 0,
        ];

        if (!$dealerId) {
            return $counts;
        }

        try {
            $inquiryRow = DB::selectOne(
                'SELECT COUNT(*) AS "CNT"
                 FROM "LEAD"
                 WHERE "ASSIGNED_TO" = ?
                   AND UPPER(TRIM(COALESCE(
                       (SELECT FIRST 1 la."STATUS"
                          FROM "LEAD_ACT" la
                         WHERE la."LEADID" = "LEAD"."LEADID"
                         ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                       "CURRENTSTATUS",
                       \'Pending\'
                   ))) NOT IN (\'COMPLETED\', \'CASE COMPLETED\', \'FAILED\', \'REWARDED\', \'REWARD\', \'REWARD DISTRIBUTED\')',
                [$dealerId]
            );
            $counts['inquiries'] = (int) ($inquiryRow->CNT ?? $inquiryRow->cnt ?? current((array) $inquiryRow) ?? 0);
        } catch (\Throwable $e) {
            $counts['inquiries'] = 0;
        }

        try {
            $pendingRow = DB::selectOne(
                'SELECT COUNT(*) AS "CNT"
                 FROM (
                     SELECT a."LEADID", a."STATUS"
                     FROM "LEAD_ACT" a
                     JOIN (
                         SELECT "LEADID", MAX("CREATIONDATE") AS max_created
                         FROM "LEAD_ACT"
                         GROUP BY "LEADID"
                     ) m ON m."LEADID" = a."LEADID" AND m.max_created = a."CREATIONDATE"
                 ) latest
                 JOIN "LEAD" l ON l."LEADID" = latest."LEADID"
                 WHERE l."ASSIGNED_TO" = ?
                   AND UPPER(TRIM(latest."STATUS")) = \'COMPLETED\'
                   AND TRIM(COALESCE(l."REFERRALCODE", \'\')) <> \'\'',
                [$dealerId]
            );
            $counts['pending_payouts'] = (int) ($pendingRow->CNT ?? $pendingRow->cnt ?? current((array) $pendingRow) ?? 0);
        } catch (\Throwable $e) {
            $counts['pending_payouts'] = 0;
        }

        return $counts;
    }

    public function reports(Request $request): View
    {
        $dealerId = $request->session()->get('user_id');
        $period = $request->get('period', 'month');
        $fromInput = $request->get('from');
        $toInput = $request->get('to');

    $dateFrom = null;
    $dateTo = null;
    $periodLabel = 'Current Month';
    $trendLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];

    $days = 0;
    if ($period === 'range' && !empty($fromInput) && !empty($toInput)) {
        $dateFrom = Carbon::parse($fromInput)->startOfDay();
        $dateTo = Carbon::parse($toInput)->endOfDay();
        $periodLabel = $dateFrom->isSameDay($dateTo)
            ? $dateFrom->format('M j, Y')
            : $dateFrom->format('M j, Y') . ' – ' . $dateTo->format('M j, Y');
        $days = (int) ($dateFrom->diffInDays($dateTo) + 1);
        $days = max(1, $days);
        $trendLabels = $days <= 7
            ? array_map(fn($i) => $dateFrom->copy()->addDays($i)->format('D'), range(0, $days - 1))
            : ($days <= 31 ? ['Week 1', 'Week 2', 'Week 3', 'Week 4'] : array_map(fn($m) => Carbon::create()->month($m)->format('M'), range(1, 12)));
    } elseif ($period === 'week') {
        $dateFrom = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $dateTo = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        $periodLabel = 'Current Week';
        $trendLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $days = 7;
    } elseif ($period === 'year') {
        $dateFrom = Carbon::now()->startOfYear();
        $dateTo = Carbon::now()->endOfYear();
        $periodLabel = 'Current Year';
        $trendLabels = array_map(fn($m) => Carbon::create()->month($m)->format('M'), range(1, 12));
    } else {
        $period = 'month';
        $dateFrom = Carbon::now()->startOfMonth();
        $dateTo = Carbon::now()->endOfMonth();
        $periodLabel = 'Current Month';
        $trendLabels = array_map(fn($i) => str_pad((string) $i, 2, '0', STR_PAD_LEFT), range(1, $dateFrom->daysInMonth));
    }

    $statusMap = [
        'PENDING' => 'PENDING', 'Pending' => 'PENDING',
        'FOLLOW UP' => 'FOLLOW UP', 'FOLLOWUP' => 'FOLLOW UP', 'FollowUp' => 'FOLLOW UP',
        'DEMO' => 'DEMO', 'Demo' => 'DEMO',
        'CONFIRMED' => 'CONFIRMED', 'Confirmed' => 'CONFIRMED', 'CASE CONFIRMED' => 'CONFIRMED',
        'COMPLETED' => 'COMPLETED', 'Completed' => 'COMPLETED', 'CASE COMPLETED' => 'COMPLETED',
        'REWARDED' => 'REWARDED', 'Rewarded' => 'REWARDED', 'REWARD' => 'REWARDED', 'REWARD DISTRIBUTED' => 'REWARDED', 'Reward Distributed' => 'REWARDED',
    ];
    $buildStatusCounts = function (?Carbon $rangeStart = null, ?Carbon $rangeEnd = null) use ($dealerId, $statusMap) {
        $counts = [
            'PENDING' => 0,
            'FOLLOW UP' => 0,
            'DEMO' => 0,
            'CONFIRMED' => 0,
            'COMPLETED' => 0,
            'REWARDED' => 0,
        ];
        if (!$dealerId) {
            return $counts;
        }

        $query = 'SELECT l."LEADID",
                COALESCE(
                    (SELECT FIRST 1 la."STATUS"
                       FROM "LEAD_ACT" la
                      WHERE la."LEADID" = l."LEADID"
                      ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                    l."CURRENTSTATUS",
                    \'Pending\'
                ) AS "LATEST_STATUS"
            FROM "LEAD" l
            WHERE l."ASSIGNED_TO" = ?';
        $bindings = [$dealerId];

        if ($rangeStart && $rangeEnd) {
            $query .= ' AND l."CREATEDAT" >= ? AND l."CREATEDAT" <= ?';
            $bindings[] = $rangeStart->format('Y-m-d H:i:s');
            $bindings[] = $rangeEnd->format('Y-m-d H:i:s');
        }

        $rows = DB::select($query, $bindings);

        foreach ($rows as $row) {
            $raw = trim($row->LATEST_STATUS ?? '');
            if (strtoupper($raw) === 'FAILED') {
                continue;
            }
            $normalized = $statusMap[strtoupper($raw)] ?? $statusMap[$raw] ?? 'PENDING';
            if (isset($counts[$normalized])) {
                $counts[$normalized]++;
            } else {
                $counts['PENDING']++;
            }
        }

        return $counts;
    };

    $statusCounts = [
        'PENDING' => 0,
        'FOLLOW UP' => 0,
        'DEMO' => 0,
        'CONFIRMED' => 0,
        'COMPLETED' => 0,
        'REWARDED' => 0,
    ];
    $totalInquiry = 0;
    $inquiryTrendData = array_fill(0, count($trendLabels), 0);
    $productCounts = array_fill(0, 11, 0);

    if ($dealerId && $dateFrom && $dateTo) {
        $df = $dateFrom->format('Y-m-d H:i:s');
        $dt = $dateTo->format('Y-m-d H:i:s');

        $totalRow = DB::selectOne(
            'SELECT COUNT(*) AS "CNT" FROM "LEAD" WHERE "ASSIGNED_TO" = ? AND "CREATEDAT" >= ? AND "CREATEDAT" <= ?',
            [$dealerId, $df, $dt]
        );
        $totalInquiry = (int) ($totalRow->CNT ?? 0);

        $numBuckets = count($trendLabels);
        if ($period === 'week' || $period === 'month' || ($period === 'range' && $days <= 7)) {
            $dayCounts = [];
            for ($i = 0; $i < $numBuckets; $i++) {
                $d = $dateFrom->copy()->addDays($i)->format('Y-m-d');
                $row = DB::selectOne(
                    'SELECT COUNT(*) AS "CNT" FROM "LEAD" WHERE "ASSIGNED_TO" = ? AND CAST("CREATEDAT" AS DATE) = ?',
                    [$dealerId, $d]
                );
                $dayCounts[] = (int) ($row->CNT ?? 0);
            }
            $inquiryTrendData = $dayCounts;
        } elseif ($period === 'year' || ($period === 'range' && $days > 31)) {
            $monthCounts = [];
            $y = $dateFrom->year;
            for ($m = 1; $m <= 12; $m++) {
                $row = DB::selectOne(
                    'SELECT COUNT(*) AS "CNT" FROM "LEAD" l WHERE l."ASSIGNED_TO" = ?
                        AND l."CREATEDAT" IS NOT NULL
                        AND l."CREATEDAT" >= ? AND l."CREATEDAT" <= ?
                        AND EXTRACT(YEAR FROM l."CREATEDAT") = ?
                        AND EXTRACT(MONTH FROM l."CREATEDAT") = ?',
                    [$dealerId, $df, $dt, $y, $m]
                );
                $monthCounts[] = (int) ($row->CNT ?? 0);
            }
            $inquiryTrendData = $monthCounts;
        } elseif ($period === 'range' && $days >= 8 && $days <= 31) {
            $bucketDays = (int) ceil($days / 4);
            $weekCounts = [];
            for ($i = 0; $i < 4; $i++) {
                $bStart = $dateFrom->copy()->addDays($i * $bucketDays)->startOfDay()->format('Y-m-d H:i:s');
                $bEnd = $dateFrom->copy()->addDays(min(($i + 1) * $bucketDays, $days) - 1)->endOfDay()->format('Y-m-d H:i:s');
                $row = DB::selectOne(
                    'SELECT COUNT(*) AS "CNT" FROM "LEAD" WHERE "ASSIGNED_TO" = ? AND "CREATEDAT" >= ? AND "CREATEDAT" <= ?',
                    [$dealerId, $bStart, $bEnd]
                );
                $weekCounts[] = (int) ($row->CNT ?? 0);
            }
            $inquiryTrendData = $weekCounts;
        }

        $statusCounts = $buildStatusCounts($dateFrom, $dateTo);

        $productRows = DB::select(
            'SELECT la."DEALTPRODUCT" FROM "LEAD_ACT" la
            WHERE la."USERID" = ? AND la."CREATIONDATE" >= ? AND la."CREATIONDATE" <= ?
            AND la."DEALTPRODUCT" IS NOT NULL AND la."DEALTPRODUCT" <> \'\'',
            [$dealerId, $df, $dt]
        );
        foreach ($productRows as $pr) {
            $val = trim($pr->DEALTPRODUCT ?? '');
            $ids = array_map('intval', array_filter(preg_split('/[\s,\(\)]+/', $val)));
            foreach ($ids as $pid) {
                if ($pid >= 1 && $pid <= 10) {
                    $productCounts[$pid - 1]++;
                } elseif ($pid >= 11) {
                    $productCounts[10]++;
                }
            }
        }
    }

    return view('dealer.reports', [
        'currentPage' => 'reports',
        'statusCounts' => $statusCounts,
        'totalInquiry' => $totalInquiry,
        'inquiryTrendData' => $inquiryTrendData,
        'trendLabels' => $trendLabels,
        'period' => $period,
        'periodLabel' => $periodLabel,
        'from' => $fromInput,
        'to' => $toInput,
        'productCounts' => $productCounts,
    ]);
}

    public function history(Request $request): View
    {
        $dealerId = $request->session()->get('user_id');
        $activities = [];
        if ($dealerId) {
            $activities = DB::select(
                'SELECT la."LEAD_ACTID", la."LEADID", la."CREATIONDATE", la."SUBJECT", la."DESCRIPTION", la."STATUS"
                FROM "LEAD_ACT" la
                JOIN "LEAD" l ON l."LEADID" = la."LEADID"
                WHERE l."ASSIGNED_TO" = ?
                ORDER BY la."LEAD_ACTID" DESC',
                [$dealerId]
            );
        }
        return view('dealer.history', ['activities' => $activities, 'currentPage' => 'history']);
    }

    public function notifications(Request $request): JsonResponse
    {
        $dealerId = $request->session()->get('user_id');
        if (!$dealerId) {
            return response()->json(['items' => []], 200);
        }

        // Assignment notifications: LEAD_ACT rows for leads currently assigned to this dealer
        // where the subject/description indicates an assignment event.
        $rows = DB::select(
            'SELECT FIRST 20
                la."LEAD_ACTID", la."LEADID", la."CREATIONDATE", la."SUBJECT", la."DESCRIPTION", u."EMAIL" AS "FROM_EMAIL",
                l."COMPANYNAME", l."CONTACTNAME"
             FROM "LEAD_ACT" la
             JOIN "LEAD" l ON l."LEADID" = la."LEADID"
             LEFT JOIN "USERS" u ON u."USERID" = la."USERID"
             WHERE l."ASSIGNED_TO" = ?
               AND (
                    UPPER(TRIM(COALESCE(la."SUBJECT", \'\'))) STARTING WITH \'LEAD ASSIGNED\'
                    OR UPPER(TRIM(COALESCE(la."DESCRIPTION", \'\'))) STARTING WITH \'LEAD ASSIGNED\'
               )
             ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC',
            [$dealerId]
        );

        $items = array_map(function ($r) {
            $leadId = (int) ($r->LEADID ?? 0);
            $createdAt = null;
            if (!empty($r->CREATIONDATE)) {
                try {
                    $createdAt = Carbon::parse($r->CREATIONDATE);
                } catch (\Throwable $e) {
                    $createdAt = Carbon::createFromTimestamp(strtotime((string) $r->CREATIONDATE));
                }
            }
            $title = 'Inquiry #SQL-' . $leadId . ' assigned';
            $who = trim((string) ($r->FROM_EMAIL ?? ''));
            $desc = trim((string) ($r->DESCRIPTION ?? ''));
            if ($desc === '' && $who !== '') {
                $desc = 'Assigned by ' . $who;
            }
            if ($desc === '') {
                $desc = 'Assigned';
            }
            return [
                'id' => (string) ($r->LEAD_ACTID ?? ''),
                'lead_id' => $leadId,
                'title' => $title,
                'description' => $desc,
                'time' => $createdAt ? $createdAt->format('Y-m-d H:i') : '',
                'target_url' => $leadId > 0
                    ? route('dealer.inquiries', ['lead' => $leadId, 'fromNotif' => (string) ($r->LEAD_ACTID ?? '')])
                    : route('dealer.inquiries'),
            ];
        }, $rows);

        return response()->json(['items' => $items]);
    }
}
