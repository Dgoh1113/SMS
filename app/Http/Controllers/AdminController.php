<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminController extends Controller
{
    private function dashboardData(): array
    {
        $leads = DB::select('SELECT COUNT(*) as c FROM "LEAD"');
        $activities = DB::select('SELECT COUNT(*) as c FROM "LEAD_ACT"');
        $payouts = DB::select('SELECT COUNT(*) as c FROM "REFERRER_PAYOUT"');
        $users = DB::select('SELECT COUNT(*) as c FROM "USERS"');
        return [
            'totalLeads' => $leads[0]->c ?? 0,
            'totalActivities' => $activities[0]->c ?? 0,
            'totalPayouts' => $payouts[0]->c ?? 0,
            'totalUsers' => $users[0]->c ?? 0,
        ];
    }

    public function dashboard(): View
    {
        return view('admin.dashboard', array_merge($this->dashboardData(), ['currentPage' => 'dashboard']));
    }

    public function inquiries(): View
    {
        $rows = DB::select(
            'SELECT FIRST 100
                "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL","CITY","POSTCODE",
                "BUSINESSNATURE","USERCOUNT","EXISTINGSOFTWARE","DEMOMODE","CURRENTSTATUS",
                "CREATEDAT","CREATEDBY","ASSIGNED_TO","LASTMODIFIED"
            FROM "LEAD"
            ORDER BY "LEADID" DESC'
        );
        return view('admin.inquiries', ['items' => $rows, 'currentPage' => 'inquiries']);
    }

    public function dealers(Request $request): View
    {
        $role = $request->query('role');
        $q = trim((string) $request->query('q', ''));

        $where = [];
        $params = [];

        if ($role && in_array($role, ['Admin', 'Dealer', 'Manager'], true)) {
            $where[] = '"SYSTEMROLE" = ?';
            $params[] = $role;
        }
        if ($q !== '') {
            $where[] = '("EMAIL" LIKE ? OR CAST("USERID" AS VARCHAR(20)) LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql = 'SELECT "USERID","EMAIL","SYSTEMROLE","ISACTIVE","LASTLOGIN"
                FROM "USERS"';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY "USERID"';

        $rows = DB::select($sql, $params);

        return view('admin.dealers', [
            'items' => $rows,
            'currentPage' => 'dealers',
            'filterRole' => $role,
            'filterQuery' => $q,
        ]);
    }

    public function dealersStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:Admin,Dealer,Manager',
            'password' => 'required|string|min:6|confirmed',
            'is_active' => 'sometimes|boolean',
        ]);

        $exists = DB::selectOne('SELECT 1 FROM "USERS" WHERE "EMAIL" = ?', [$data['email']]);
        if ($exists) {
            return back()->withInput()->with('error', 'Email already exists.');
        }

        $isActive = !empty($data['is_active']) ? 1 : 0;
        $hash = Hash::make($data['password']);

        DB::insert(
            'INSERT INTO "USERS" ("EMAIL","PASSWORDHASH","SYSTEMROLE","ISACTIVE") VALUES (?,?,?,?)',
            [$data['email'], $hash, $data['role'], $isActive]
        );

        return redirect()->route('admin.dealers')->with('success', 'User created.');
    }

    public function dealersUpdate(Request $request, int $userId): RedirectResponse
    {
        $action = $request->input('action', 'save');

        if ($action === 'delete') {
            DB::delete('DELETE FROM "USERS" WHERE "USERID" = ?', [$userId]);
            return redirect()->route('admin.dealers')->with('success', 'User deleted.');
        }

        if ($action === 'ban') {
            DB::update('UPDATE "USERS" SET "ISACTIVE" = 0 WHERE "USERID" = ?', [$userId]);
            return redirect()->route('admin.dealers')->with('success', 'User banned.');
        }

        $data = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:Admin,Dealer,Manager',
            'password' => 'nullable|string|min:6|confirmed',
            'is_active' => 'sometimes|boolean',
        ]);

        $isActive = !empty($data['is_active']) ? 1 : 0;

        DB::update(
            'UPDATE "USERS" SET "EMAIL" = ?, "SYSTEMROLE" = ?, "ISACTIVE" = ? WHERE "USERID" = ?',
            [$data['email'], $data['role'], $isActive, $userId]
        );

        if (!empty($data['password'])) {
            DB::update(
                'UPDATE "USERS" SET "PASSWORDHASH" = ? WHERE "USERID" = ?',
                [Hash::make($data['password']), $userId]
            );
        }

        return redirect()->route('admin.dealers')->with('success', 'User updated.');
    }

    public function rewards(): View
    {
        $rows = DB::select(
            'SELECT FIRST 100
                "REFERRERPAYOUTID","DEALSUBMISSIONID","USERID","REFERRERID","STATUS","DATEGENERATED","DATEPAID"
            FROM "REFERRER_PAYOUT"
            ORDER BY "REFERRERPAYOUTID" DESC'
        );
        return view('admin.rewards', ['items' => $rows, 'currentPage' => 'rewards']);
    }

    public function reports(): View
    {
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
            'SELECT "CURRENTSTATUS" AS status, COUNT(*) AS c FROM "LEAD" GROUP BY "CURRENTSTATUS"'
        );
        $leadStatus = [
            'Open' => 0,
            'Ongoing' => 0,
            'Closed' => 0,
        ];
        foreach ($leadStatusRows as $row) {
            $key = (string) $get($row, 'status');
            if (isset($leadStatus[$key])) {
                $leadStatus[$key] = (int) $get($row, 'c');
            }
        }

        // Unassigned vs assigned leads
        $unassigned = DB::selectOne('SELECT COUNT(*) AS c FROM "LEAD" WHERE "ASSIGNED_TO" IS NULL');

        $pendingActs = DB::select(
            'SELECT "STATUS" AS status, COUNT(*) AS c FROM "LEAD_ACT" GROUP BY "STATUS"'
        );
        $activityStatus = [
            'Pending' => 0,
            'FollowUp' => 0,
            'Demo' => 0,
            'Confirmed' => 0,
            'Completed' => 0,
            'reward' => 0,
        ];
        foreach ($pendingActs as $row) {
            $key = (string) $get($row, 'status');
            if (isset($activityStatus[$key])) {
                $activityStatus[$key] = (int) $get($row, 'c');
            }
        }

        // Last month activity by status (LEAD_ACT has CREATIONDATE)
        $lastMonthActs = DB::select(
            'SELECT "STATUS" AS status, COUNT(*) AS c FROM "LEAD_ACT"
             WHERE EXTRACT(YEAR FROM "CREATIONDATE") = EXTRACT(YEAR FROM DATEADD(MONTH, -1, CURRENT_DATE))
               AND EXTRACT(MONTH FROM "CREATIONDATE") = EXTRACT(MONTH FROM DATEADD(MONTH, -1, CURRENT_DATE))
             GROUP BY "STATUS"'
        );
        $lastMonthActivity = [
            'Pending' => 0,
            'FollowUp' => 0,
            'Demo' => 0,
            'Confirmed' => 0,
            'Completed' => 0,
            'reward' => 0,
        ];
        foreach ($lastMonthActs as $row) {
            $key = (string) $get($row, 'status');
            if (isset($lastMonthActivity[$key])) {
                $lastMonthActivity[$key] = (int) $get($row, 'c');
            }
        }

        // Payout summary
        $payoutRows = DB::select(
            'SELECT "STATUS" AS status, COUNT(*) AS c FROM "REFERRER_PAYOUT" GROUP BY "STATUS"'
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
            'SELECT "STATUS" AS status, COUNT(*) AS c FROM "REFERRER_PAYOUT"
             WHERE EXTRACT(YEAR FROM "DATEGENERATED") = EXTRACT(YEAR FROM DATEADD(MONTH, -1, CURRENT_DATE))
               AND EXTRACT(MONTH FROM "DATEGENERATED") = EXTRACT(MONTH FROM DATEADD(MONTH, -1, CURRENT_DATE))
             GROUP BY "STATUS"'
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
            'SELECT EXTRACT(DAY FROM "CREATEDAT") AS d, COUNT(*) AS c
             FROM "LEAD"
             WHERE EXTRACT(MONTH FROM "CREATEDAT") = EXTRACT(MONTH FROM CURRENT_TIMESTAMP)
               AND EXTRACT(YEAR FROM "CREATEDAT") = EXTRACT(YEAR FROM CURRENT_TIMESTAMP)
             GROUP BY EXTRACT(DAY FROM "CREATEDAT")
             ORDER BY d'
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
            'SELECT COUNT(*) AS c FROM "LEAD"
             WHERE EXTRACT(YEAR FROM "CREATEDAT") = EXTRACT(YEAR FROM DATEADD(MONTH, -1, CURRENT_DATE))
               AND EXTRACT(MONTH FROM "CREATEDAT") = EXTRACT(MONTH FROM DATEADD(MONTH, -1, CURRENT_DATE))'
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
            'unassigned' => $percentChange($currentMonthTotal, $lastMonthTotal),
            'Pending' => $percentChange($activityStatus['Pending'] ?? 0, $lastMonthActivity['Pending'] ?? 0),
            'FollowUp' => $percentChange($activityStatus['FollowUp'] ?? 0, $lastMonthActivity['FollowUp'] ?? 0),
            'Demo' => $percentChange($activityStatus['Demo'] ?? 0, $lastMonthActivity['Demo'] ?? 0),
            'Confirmed' => $percentChange($activityStatus['Confirmed'] ?? 0, $lastMonthActivity['Confirmed'] ?? 0),
            'Completed' => $percentChange($activityStatus['Completed'] ?? 0, $lastMonthActivity['Completed'] ?? 0),
            'Pending Reward' => $percentChange($payoutStatus['Pending'] ?? 0, $lastMonthPayout['Pending'] ?? 0),
            'Rewarded' => $percentChange($payoutStatus['Paid'] ?? 0, $lastMonthPayout['Paid'] ?? 0),
        ];

        // Product conversion by ProductID
        $productRows = DB::select(
            'SELECT "PRODUCTID" AS product_id, COUNT(*) AS c
             FROM "LEAD"
             WHERE "PRODUCTID" IS NOT NULL
             GROUP BY "PRODUCTID"
             ORDER BY "PRODUCTID"'
        );
        $productLabels = [
            1 => 'SQL Account',
            2 => 'SQL Payroll',
            3 => 'Inventory Pro',
            4 => 'Cloud Sync',
            5 => 'SQL Suite',
        ];
        $productConversion = [];
        foreach ($productRows as $row) {
            $pid = (int) $get($row, 'product_id');
            $productConversion[] = [
                'label' => $productLabels[$pid] ?? 'Product ' . $pid,
                'count' => (int) $get($row, 'c'),
            ];
        }

        return view('admin.reports', [
            'currentPage' => 'reports',
            'leadStatus' => $leadStatus,
            'unassignedLeads' => (int) ($unassigned->c ?? 0),
            'activityStatus' => $activityStatus,
            'payoutStatus' => $payoutStatus,
            'metricPercent' => $metricPercent,
            'inquiryTrend' => $inquiryTrend,
            'inquiryTrendPercentChange' => $inquiryTrendPercentChange,
            'productConversion' => $productConversion,
        ]);
    }

    public function reportsV2(): View
    {
        // Dynamic: derive metrics from LEAD / LEAD_ACT / USERS
        $dealerTotals = DB::select(
            'SELECT l."ASSIGNED_TO" AS dealer_id, u."EMAIL" AS email,
                    COUNT(*) AS total_c,
                    SUM(CASE WHEN l."CURRENTSTATUS" = ? THEN 1 ELSE 0 END) AS closed_c
             FROM "LEAD" l
             JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
             WHERE l."ASSIGNED_TO" IS NOT NULL
             GROUP BY l."ASSIGNED_TO", u."EMAIL"',
            ['Closed']
        );

        $totalsByDealer = [];
        foreach ($dealerTotals as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '') continue;
            $total = (int) ($r->TOTAL_C ?? $r->total_c ?? 0);
            $closed = (int) ($r->CLOSED_C ?? $r->closed_c ?? 0);
            $totalsByDealer[$id] = [
                'dealer_id' => $id,
                'email' => (string) ($r->EMAIL ?? $r->email ?? $id),
                'total' => $total,
                'closed' => $closed,
                'closed_rate' => $total > 0 ? ($closed / $total * 100) : 0,
                'rejected' => 0,
                'rejection_rate' => 0,
            ];
        }

        // "Rejection" proxy: Closed leads without any Completed activity record
        $rejectedRows = DB::select(
            'SELECT l."ASSIGNED_TO" AS dealer_id, COUNT(*) AS c
             FROM "LEAD" l
             WHERE l."ASSIGNED_TO" IS NOT NULL
               AND l."CURRENTSTATUS" = ?
               AND NOT EXISTS (
                    SELECT 1 FROM "LEAD_ACT" a
                    WHERE a."LEADID" = l."LEADID" AND a."STATUS" = ?
               )
             GROUP BY l."ASSIGNED_TO"',
            ['Closed', 'Completed']
        );
        foreach ($rejectedRows as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '' || !isset($totalsByDealer[$id])) continue;
            $rej = (int) ($r->C ?? $r->c ?? 0);
            $totalsByDealer[$id]['rejected'] = $rej;
            $total = (int) $totalsByDealer[$id]['total'];
            $totalsByDealer[$id]['rejection_rate'] = $total > 0 ? ($rej / $total * 100) : 0;
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

        // Variance %: last 90 days vs same period last year, per dealer
        $varianceRows = DB::select(
            'SELECT l."ASSIGNED_TO" AS dealer_id,
                    SUM(CASE WHEN l."CREATEDAT" >= DATEADD(DAY, -90, CURRENT_DATE) AND l."CREATEDAT" <= CURRENT_DATE THEN 1 ELSE 0 END) AS curr_c,
                    SUM(CASE WHEN l."CREATEDAT" >= DATEADD(YEAR, -1, DATEADD(DAY, -90, CURRENT_DATE)) AND l."CREATEDAT" <= DATEADD(YEAR, -1, CURRENT_DATE) THEN 1 ELSE 0 END) AS last_c
             FROM "LEAD" l
             WHERE l."ASSIGNED_TO" IS NOT NULL
             GROUP BY l."ASSIGNED_TO"'
        );

        $variance = [];
        foreach ($varianceRows as $r) {
            $id = (string) ($r->DEALER_ID ?? $r->dealer_id ?? '');
            if ($id === '' || !isset($totalsByDealer[$id])) continue;
            $curr = (int) ($r->CURR_C ?? $r->curr_c ?? 0);
            $last = (int) ($r->LAST_C ?? $r->last_c ?? 0);
            $pct = $last > 0 ? (int) round(($curr - $last) / $last * 100) : ($curr > 0 ? 100 : 0);
            $variance[] = [
                'dealer_id' => $id,
                'name' => $totalsByDealer[$id]['email'],
                'delta' => $pct,
            ];
        }
        usort($variance, function ($a, $b) {
            return abs($b['delta']) <=> abs($a['delta']);
        });
        $variance = array_slice($variance, 0, 10);

        // Action list (at-risk): largest negative variance dealers
        $neg = array_values(array_filter($variance, fn ($v) => $v['delta'] < 0));
        usort($neg, fn ($a, $b) => $a['delta'] <=> $b['delta']);
        $neg = array_slice($neg, 0, 8);

        $atRisk = [];
        foreach ($neg as $v) {
            $id = $v['dealer_id'];
            $atRisk[] = [
                'name' => $totalsByDealer[$id]['email'],
                'id' => $id,
                'comp' => 0,
                'primary' => 0,
                'variance' => $v['delta'],
                'variance_pct' => (float) $v['delta'],
                'last_activity' => '—',
            ];
        }

        return view('admin.reports_v2', [
            'currentPage' => 'reports',
            'topVariance' => $variance,
            'highestClosed' => $highestClosed,
            'highestRejected' => $highestRejected,
            'atRisk' => $atRisk,
        ]);
    }

    public function reportsRevenue(Request $request): View
    {
        $quarter = strtoupper((string) $request->query('quarter', ''));
        $year = (int) $request->query('year', (int) now()->format('Y'));

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

        // Dealer performance for selected quarter
        $rows = DB::select(
            'SELECT u."USERID" AS dealer_id,
                    u."EMAIL" AS email,
                    COUNT(*) AS total_leads,
                    SUM(CASE WHEN l."CURRENTSTATUS" IN (?, ?) THEN 1 ELSE 0 END) AS closed_leads,
                    SUM(CASE WHEN l."CURRENTSTATUS" = ? THEN 1 ELSE 0 END) AS rewarded_leads
             FROM "LEAD" l
             JOIN "USERS" u ON u."USERID" = l."ASSIGNED_TO"
             WHERE l."ASSIGNED_TO" IS NOT NULL
               AND l."CREATEDAT" >= ?
               AND l."CREATEDAT" <= ?
             GROUP BY u."USERID", u."EMAIL"
             ORDER BY total_leads DESC',
            ['Completed', 'reward', 'reward', $startStr, $endStr]
        );

        $dealers = [];
        $totalVolume = 0;
        $totalLeads = 0;
        $weightedRejection = 0;

        foreach ($rows as $r) {
            $total = (int) ($r->TOTAL_LEADS ?? $r->total_leads ?? 0);
            $closed = (int) ($r->CLOSED_LEADS ?? $r->closed_leads ?? 0);
            $rewarded = (int) ($r->REWARDED_LEADS ?? $r->rewarded_leads ?? 0);
            if ($total <= 0) {
                continue;
            }
            $rejectionRate = $total > 0 ? (1 - ($closed / $total)) * 100 : 0;
            // Simple revenue proxy: closed leads x 1,000
            $revenue = $closed * 1000;

            $dealers[] = [
                'dealer_id' => (int) ($r->DEALER_ID ?? $r->dealer_id ?? 0),
                'email' => (string) ($r->EMAIL ?? $r->email ?? ''),
                'total' => $total,
                'closed' => $closed,
                'rewarded' => $rewarded,
                'rejection_rate' => $rejectionRate,
                'revenue' => $revenue,
            ];

            $totalVolume += $total;
            $totalLeads += $total;
            $weightedRejection += $rejectionRate * $total;
        }

        usort($dealers, fn ($a, $b) => $b['revenue'] <=> $a['revenue']);
        $topDealer = $dealers[0] ?? null;
        $avgRejection = $totalLeads > 0 ? $weightedRejection / $totalLeads : 0.0;

        // Chart: top 5 dealers by revenue
        $chartDealers = array_slice($dealers, 0, 5);
        $chartLabels = array_column($chartDealers, 'email');
        $chartVolume = array_column($chartDealers, 'total');
        $chartClosed = array_column($chartDealers, 'closed');
        $chartRewarded = array_column($chartDealers, 'rewarded');

        // Rankings table: same top 5 dealers
        $rankings = $chartDealers;

        return view('admin.reports_revenue', [
            'currentPage' => 'reports',
            'selectedQuarter' => $quarter,
            'selectedYear' => $year,
            'yearOptions' => range(((int) now()->format('Y')) - 4, ((int) now()->format('Y'))),
            'totalVolume' => $totalVolume,
            'avgRejectionRate' => $avgRejection,
            'topDealer' => $topDealer,
            'chartLabels' => $chartLabels,
            'chartVolume' => $chartVolume,
            'chartClosed' => $chartClosed,
            'chartRewarded' => $chartRewarded,
            'rankings' => $rankings,
        ]);
    }

    public function history(): View
    {
        $rows = DB::select(
            'SELECT FIRST 100
                "LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS"
            FROM "LEAD_ACT"
            ORDER BY "LEAD_ACTID" DESC'
        );
        return view('admin.history', ['items' => $rows, 'currentPage' => 'history']);
    }

    public function fulldatabase(): View
    {
        $tables = [
            'lead' => DB::select('SELECT FIRST 200 * FROM "LEAD" ORDER BY "LEADID" DESC'),
            'lead_act' => DB::select('SELECT FIRST 200 * FROM "LEAD_ACT" ORDER BY "LEAD_ACTID" DESC'),
            'referrer_payout' => DB::select('SELECT FIRST 200 * FROM "REFERRER_PAYOUT" ORDER BY "REFERRERPAYOUTID" DESC'),
            'users' => DB::select('SELECT FIRST 200 * FROM "USERS" ORDER BY "USERID" DESC'),
            'user_passkey' => DB::select('SELECT FIRST 200 * FROM "USER_PASSKEY" ORDER BY "USER_PASSKEYID" DESC'),
        ];
        return view('admin.fulldatabase', ['tables' => $tables, 'currentPage' => 'fulldatabase']);
    }
}
