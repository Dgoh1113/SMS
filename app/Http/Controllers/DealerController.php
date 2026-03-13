<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DealerController extends Controller
{
    public function dashboard(Request $request): View
    {
        $dealerId = $request->session()->get('user_id');
        $leads = [];
        $metrics = [
            'activeInquiries' => 0,
            'activeInquiriesTrend' => '+12%',
            'conversionRate' => '0%',
            'conversionTrend' => '-2%',
            'closedCaseCount' => 0,
            'demosTrend' => '+5',
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
        $inquiriesPerPage = 6;
        $leadsPaginated = [];

        if ($dealerId) {
            $dealerEmail = trim((string) ($request->session()->get('user_email') ?? ''));
            if (!$dealerEmail) {
                $emailRow = DB::selectOne('SELECT "EMAIL" FROM "USERS" WHERE "USERID" = ?', [$dealerId]);
                $dealerEmail = trim((string) ($emailRow->EMAIL ?? ''));
            }

            $leadsRaw = DB::select(
                'SELECT FIRST 50
                    l."LEADID", l."PRODUCTID", l."COMPANYNAME", l."CONTACTNAME", l."CONTACTNO", l."EMAIL",
                    l."CITY", l."POSTCODE", l."BUSINESSNATURE", l."USERCOUNT", l."EXISTINGSOFTWARE", l."DEMOMODE",
                    l."DESCRIPTION", l."REFERRALCODE", l."CREATEDAT", l."CREATEDBY",
                    l."ASSIGNED_TO", l."LASTMODIFIED",
                    u."EMAIL" AS "ASSIGNED_BY_EMAIL",
                    COALESCE(
                        (SELECT FIRST 1 la."STATUS" FROM "LEAD_ACT" la WHERE la."LEADID" = l."LEADID" ORDER BY la."CREATIONDATE" DESC),
                        l."CURRENTSTATUS",
                        \'Pending\'
                    ) AS "ACT_STATUS",
                    (SELECT FIRST 1 la."CREATIONDATE" FROM "LEAD_ACT" la WHERE la."LEADID" = l."LEADID" ORDER BY la."CREATIONDATE" DESC) AS "ACT_LAST_UPDATE"
                FROM "LEAD" l
                LEFT JOIN "USERS" u ON u."USERID" = l."CREATEDBY"
                WHERE l."ASSIGNED_TO" = ?
                ORDER BY l."LEADID" DESC',
                [$dealerId]
            );
            $leads = array_values(array_filter($leadsRaw, function ($l) {
                $s = strtoupper(trim($l->ACT_STATUS ?? $l->CURRENTSTATUS ?? ''));
                return $s !== 'FAILED';
            }));

            $activeCountRow = DB::selectOne(
                'SELECT COUNT(*) AS "CNT" FROM "LEAD"
                WHERE "ASSIGNED_TO" = ? AND UPPER(TRIM(COALESCE("CURRENTSTATUS", \'\'))) = ?',
                [$dealerId, 'ONGOING']
            );
            $activeInquiriesCount = (int) ($activeCountRow->CNT ?? 0);
            $totalAssignedCount = count($leads);

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
                             ORDER BY la2."CREATIONDATE" DESC
                         ) AS "LATEST_STATUS"
                     FROM "LEAD_ACT" la
                     WHERE la."USERID" = ?
                 ) x
                 WHERE UPPER(TRIM(COALESCE(x."LATEST_STATUS", \'\'))) IN (\'COMPLETED\', \'REWARDED\')',
                [$dealerId]
            );
            $closedCount = (int) ($closedCountRow->CNT ?? 0);
            $conversion = $totalAssignedCount > 0 ? round(($closedCount / $totalAssignedCount) * 100, 1) : 0;

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
                'activeInquiriesTrend' => '+12%',
                'conversionRate' => $conversion,
                'conversionTrend' => '-2%',
                'closedCaseCount' => $closedCount,
                'demosTrend' => '+5',
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
                            $time = max(1, $mins) . 'm late';
                        } elseif ($hours < 24) {
                            $time = $hours . 'h late';
                        } else {
                            $time = $days . ' day' . ($days !== 1 ? 's' : '') . ' overdue';
                        }
                    } else {
                        $status = 'DUE SOON';
                        $untilSec = $nextFollowUp - $now;
                        $mins = max(0, (int) floor($untilSec / 60));
                        $hours = max(0, (int) floor($untilSec / 3600));
                        $days = max(0, (int) floor($untilSec / 86400));
                        if ($mins < 60) {
                            $time = 'In ' . max(1, $mins) . 'm';
                        } elseif ($hours < 24) {
                            $time = 'In ' . $hours . 'h';
                        } elseif ($days < 2) {
                            $time = 'In 1 day';
                        } else {
                            $time = 'In ' . $days . ' days';
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
                ])
                ->values()
                ->all();
        }

        $inquiriesPerPage = 6;
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
        $leads = [];
        if ($dealerId) {
            $leads = DB::select(
                'SELECT FIRST 200
                    l."LEADID", l."PRODUCTID", l."COMPANYNAME", l."CONTACTNAME", l."CONTACTNO", l."EMAIL",
                    l."CITY", l."POSTCODE", l."BUSINESSNATURE", l."USERCOUNT", l."EXISTINGSOFTWARE", l."DEMOMODE",
                    l."DESCRIPTION", l."REFERRALCODE", l."CREATEDAT", l."CREATEDBY",
                    l."ASSIGNED_TO", l."LASTMODIFIED",
                    u."EMAIL" AS "ASSIGNED_BY_EMAIL",
                    COALESCE(
                        (SELECT FIRST 1 la."STATUS" FROM "LEAD_ACT" la WHERE la."LEADID" = l."LEADID" ORDER BY la."CREATIONDATE" DESC),
                        l."CURRENTSTATUS",
                        \'Pending\'
                    ) AS "ACT_STATUS"
                FROM "LEAD" l
                LEFT JOIN "USERS" u ON u."USERID" = l."CREATEDBY"
                WHERE l."ASSIGNED_TO" = ?
                ORDER BY l."LEADID" DESC',
                [$dealerId]
            );
        }
        return view('dealer.inquiries', ['leads' => $leads, 'currentPage' => 'inquiries']);
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

    public function inquiryFailedDescription(Request $request, int $leadId): JsonResponse
    {
        $dealerId = $request->session()->get('user_id');
        if (!$dealerId) {
            return response()->json(['description' => ''], 200);
        }
        $lead = DB::selectOne('SELECT "LEADID" FROM "LEAD" WHERE "LEADID" = ? AND "ASSIGNED_TO" = ?', [$leadId, $dealerId]);
        if (!$lead) {
            return response()->json(['description' => ''], 404);
        }
        $row = DB::selectOne(
            'SELECT FIRST 1 la."DESCRIPTION" FROM "LEAD_ACT" la
             WHERE la."LEADID" = ? AND UPPER(TRIM(COALESCE(la."STATUS", \'\'))) = \'FAILED\'
             ORDER BY la."CREATIONDATE" DESC',
            [$leadId]
        );
        $description = trim($row->DESCRIPTION ?? '');
        return response()->json(['description' => $description]);
    }

    public function inquiryActivity(Request $request, int $leadId): JsonResponse
    {
        $dealerId = $request->session()->get('user_id');
        if (!$dealerId) {
            return response()->json(['activities' => []], 200);
        }

        $lead = DB::selectOne('SELECT "LEADID" FROM "LEAD" WHERE "LEADID" = ? AND "ASSIGNED_TO" = ?', [$leadId, $dealerId]);
        if (!$lead) {
            return response()->json(['activities' => []], 200);
        }

        $activities = [];
        $rows = DB::select(
            'SELECT la."CREATIONDATE", la."SUBJECT", la."DESCRIPTION", la."STATUS", u."EMAIL" AS "USER_EMAIL"
             FROM "LEAD_ACT" la
             LEFT JOIN "USERS" u ON u."USERID" = la."USERID"
             WHERE la."LEADID" = ?
             ORDER BY la."CREATIONDATE" ASC',
            [$leadId]
        );

        foreach ($rows as $r) {
            $status = trim($r->STATUS ?? '');
            if (strtoupper($status) === 'CREATED') {
                continue;
            }

            // Normalize activity timestamp to an ISO‑8601 string in the app's timezone
            // so the frontend can reliably compute \"X min ago\" from the user's \"now\".
            $createdAtIso = null;
            if (!empty($r->CREATIONDATE)) {
                try {
                    $createdAtIso = Carbon::parse($r->CREATIONDATE)->toIso8601String();
                } catch (\Throwable $e) {
                    $createdAtIso = (string) $r->CREATIONDATE;
                }
            }

            $activities[] = [
                'type' => 'activity',
                'user' => trim($r->USER_EMAIL ?? 'System'),
                'subject' => trim($r->SUBJECT ?? ''),
                'description' => trim($r->DESCRIPTION ?? ''),
                'status' => $status,
                'created_at' => $createdAtIso,
            ];
        }

        usort($activities, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

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
        ]);
    }

    public function updateInquiryStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => 'required|integer',
            'status' => 'required|string|max:50',
            'remark' => 'nullable|string|max:4000',
            'products' => 'nullable|array',
            'products.*.id' => 'nullable',
            'products.*.name' => 'nullable|string|max:100',
        ]);

        $dealerId = $request->session()->get('user_id');
        if (!$dealerId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $leadId = (int) $validated['lead_id'];
        $lead = DB::selectOne('SELECT "LEADID" FROM "LEAD" WHERE "LEADID" = ? AND "ASSIGNED_TO" = ?', [$leadId, $dealerId]);
        if (!$lead) {
            return response()->json(['success' => false, 'message' => 'Lead not found or not assigned to you'], 404);
        }

        $status = trim($validated['status']);
        $remark = trim($validated['remark'] ?? '');
        $products = $validated['products'] ?? [];
        if (strtoupper($this->mapStatusToDb($status)) === 'COMPLETED' && empty($products)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one product for COMPLETED status.',
            ], 422);
        }
        $statusDb = $this->mapStatusToDb($status);

        $lastAct = DB::selectOne(
            'SELECT FIRST 1 la."STATUS" FROM "LEAD_ACT" la WHERE la."LEADID" = ? ORDER BY la."CREATIONDATE" DESC',
            [$leadId]
        );
        $fromStatus = $lastAct ? trim($lastAct->STATUS ?? '') : 'Pending';

        $fromUpper = strtoupper($fromStatus);
        if (in_array(strtoupper($statusDb), ['DEMO'], true)) {
            $allowedFrom = ['FOLLOW UP', 'FOLLOWUP'];
            if (!in_array($fromUpper, $allowedFrom, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must complete the follow-up (status: FOLLOW UP) before updating to DEMO. Please update the status to FOLLOW UP first.',
                ], 422);
            }
        }
        if (in_array(strtoupper($statusDb), ['REWARDED', 'REWARD DISTRIBUTED'], true)) {
            $allowedFrom = ['COMPLETED', 'REWARDED', 'REWARD DISTRIBUTED'];
            if (!in_array($fromUpper, $allowedFrom, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must complete the inquiry (status: COMPLETED) before updating to REWARDED. Please update the status to COMPLETED first.',
                ], 422);
            }
        }

        $toStatus = $statusDb;
        $description = $remark !== '' ? $remark : ('Update Status from ' . $fromStatus . ' to ' . $toStatus);
        if (! empty($products)) {
            $productNames = array_map(fn ($p) => $p['name'] ?? 'Product ' . ($p['id'] ?? ''), $products);
            $description = 'Products: ' . implode(', ', $productNames) . "\n\n" . $description;
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
                 VALUES (GEN_ID("GEN_LEAD_ACTID", 1),?,?,CURRENT_TIMESTAMP,?,?,?,?,?)',
                [$leadId, $dealerId, 'Updated Status', $description, null, $statusDb, $dealtProduct]
            );
        } else {
            DB::insert(
                'INSERT INTO "LEAD_ACT" ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS")
                 VALUES (GEN_ID("GEN_LEAD_ACTID", 1),?,?,CURRENT_TIMESTAMP,?,?,?,?)',
                [$leadId, $dealerId, 'Updated Status', $description, null, $statusDb]
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

    public function demo(Request $request): View
    {
        $dealerId = $request->session()->get('user_id');
        $leads = [];
        if ($dealerId) {
            $leads = DB::select(
                'SELECT FIRST 100
                    "LEADID","COMPANYNAME","CONTACTNAME","EMAIL","CURRENTSTATUS","DEMOMODE","CREATEDAT","LASTMODIFIED"
                FROM "LEAD"
                WHERE "ASSIGNED_TO" = ?
                ORDER BY "LEADID" DESC',
                [$dealerId]
            );
        }
        return view('dealer.demo', ['leads' => $leads, 'currentPage' => 'demo']);
    }

    public function rewards(Request $request): View
    {
        return view('dealer.rewards', ['currentPage' => 'rewards']);
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
            $trendLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        }

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
            if ($period === 'week' || ($period === 'range' && $days <= 7)) {
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
            } else {
                $trendRows = DB::select(
                    'SELECT
                        SUM(CASE WHEN EXTRACT(DAY FROM l."CREATEDAT") BETWEEN 1 AND 7 THEN 1 ELSE 0 END) AS "W1",
                        SUM(CASE WHEN EXTRACT(DAY FROM l."CREATEDAT") BETWEEN 8 AND 14 THEN 1 ELSE 0 END) AS "W2",
                        SUM(CASE WHEN EXTRACT(DAY FROM l."CREATEDAT") BETWEEN 15 AND 21 THEN 1 ELSE 0 END) AS "W3",
                        SUM(CASE WHEN EXTRACT(DAY FROM l."CREATEDAT") >= 22 THEN 1 ELSE 0 END) AS "W4"
                    FROM "LEAD" l
                    WHERE l."ASSIGNED_TO" = ?
                        AND l."CREATEDAT" IS NOT NULL
                        AND l."CREATEDAT" >= ? AND l."CREATEDAT" <= ?
                        AND EXTRACT(YEAR FROM l."CREATEDAT") = ?
                        AND EXTRACT(MONTH FROM l."CREATEDAT") = ?',
                    [$dealerId, $df, $dt, $dateFrom->year, $dateFrom->month]
                );
                if (!empty($trendRows)) {
                    $t = $trendRows[0];
                    $inquiryTrendData = [(int) ($t->W1 ?? 0), (int) ($t->W2 ?? 0), (int) ($t->W3 ?? 0), (int) ($t->W4 ?? 0)];
                }
            }

            $rows = DB::select(
                'SELECT l."LEADID",
                    COALESCE(
                        (SELECT FIRST 1 la."STATUS" FROM "LEAD_ACT" la WHERE la."LEADID" = l."LEADID" ORDER BY la."CREATIONDATE" DESC),
                        l."CURRENTSTATUS",
                        \'Pending\'
                    ) AS "LATEST_STATUS"
                FROM "LEAD" l
                WHERE l."ASSIGNED_TO" = ? AND l."CREATEDAT" >= ? AND l."CREATEDAT" <= ?',
                [$dealerId, $df, $dt]
            );
            $statusMap = [
                'PENDING' => 'PENDING', 'Pending' => 'PENDING',
                'FOLLOW UP' => 'FOLLOW UP', 'FOLLOWUP' => 'FOLLOW UP', 'FollowUp' => 'FOLLOW UP',
                'DEMO' => 'DEMO', 'Demo' => 'DEMO',
                'CONFIRMED' => 'CONFIRMED', 'Confirmed' => 'CONFIRMED', 'CASE CONFIRMED' => 'CONFIRMED',
                'COMPLETED' => 'COMPLETED', 'Completed' => 'COMPLETED', 'CASE COMPLETED' => 'COMPLETED',
                'REWARDED' => 'REWARDED', 'Rewarded' => 'REWARDED', 'REWARD' => 'REWARDED', 'REWARD DISTRIBUTED' => 'REWARDED', 'Reward Distributed' => 'REWARDED',
            ];
            foreach ($rows as $r) {
                $raw = trim($r->LATEST_STATUS ?? '');
                if (strtoupper($raw) === 'FAILED') {
                    continue;
                }
                $normalized = $statusMap[strtoupper($raw)] ?? $statusMap[$raw] ?? 'PENDING';
                if (isset($statusCounts[$normalized])) {
                    $statusCounts[$normalized]++;
                } else {
                    $statusCounts['PENDING']++;
                }
            }

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
}
