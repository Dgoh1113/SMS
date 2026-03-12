<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminController extends Controller
{
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

        $dealerStats = [];
        try {
            // Top Active Dealers (USERS + LEAD) per requested logic:
            // Leads: COUNT(*) from LEAD where ASSIGNED_TO = dealer
            // Closed: COUNT(*) where ASSIGNED_TO = dealer and CURRENTSTATUS = 'Closed'
            // Conversion: Closed / Leads
            // Pull dealer list first (company is display name).
            // Some schemas may not have COMPANY populated; still return rows.
            $topDealersRaw = [];
            try {
                $topDealersRaw = DB::select(
                    'SELECT u."USERID", u."EMAIL", u."COMPANY" AS "COMPANY", u."POSTCODE" AS "POSTCODE", u."CITY" AS "CITY"
                     FROM "USERS" u
                     WHERE UPPER(TRIM(u."SYSTEMROLE")) LIKE \'%DEALER%\''
                );
            } catch (\Throwable $e) {
                // Fallback if COMPANY column is unavailable for any reason.
                $topDealersRaw = DB::select(
                    'SELECT u."USERID", u."EMAIL", \'\' AS "COMPANY", \'\' AS "POSTCODE", \'\' AS "CITY"
                     FROM "USERS" u
                     WHERE UPPER(TRIM(u."SYSTEMROLE")) LIKE \'%DEALER%\''
                );
            }

            $dealerStats = collect($topDealersRaw)->map(function ($d) {
                $userId = (string) ($d->USERID ?? '');
                $leadsRow = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD" WHERE TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50))) = TRIM(CAST(? AS VARCHAR(50)))',
                    [$userId]
                );
                $closedRow = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD" WHERE TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50))) = TRIM(CAST(? AS VARCHAR(50))) AND "CURRENTSTATUS" = \'Closed\'',
                    [$userId]
                );
                $failedRow = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD" WHERE TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50))) = TRIM(CAST(? AS VARCHAR(50))) AND UPPER(TRIM("CURRENTSTATUS")) = \'FAILED\'',
                    [$userId]
                );
                $leads = (int) ($leadsRow->c ?? $leadsRow->C ?? current((array) $leadsRow) ?? 0);
                $closed = (int) ($closedRow->c ?? $closedRow->C ?? current((array) $closedRow) ?? 0);
                $failed = (int) ($failedRow->c ?? $failedRow->C ?? current((array) $failedRow) ?? 0);
                $conversion = $leads > 0 ? ($closed / $leads) : 0;
                $company = trim((string) ($d->COMPANY ?? ''));

                // Avg. Closing Time: average(Completed.CREATIONDATE - Pending.CREATIONDATE) per LEADID for this dealer.
                $avgClosingSeconds = null;
                try {
                    $rows = DB::select(
                        'SELECT
                            a."LEADID" AS lead_id,
                            MIN(CASE WHEN a."STATUS" = \'Pending\' THEN a."CREATIONDATE" END) AS pending_at,
                            MIN(CASE WHEN a."STATUS" = \'Completed\' THEN a."CREATIONDATE" END) AS completed_at
                         FROM "LEAD_ACT" a
                         JOIN "LEAD" l ON l."LEADID" = a."LEADID"
                         WHERE TRIM(CAST(l."ASSIGNED_TO" AS VARCHAR(50))) = TRIM(CAST(? AS VARCHAR(50)))
                         GROUP BY a."LEADID"',
                        [$userId]
                    );
                    $total = 0;
                    $count = 0;
                    foreach ($rows as $r) {
                        $p = $r->pending_at ?? $r->PENDING_AT ?? null;
                        $cAt = $r->completed_at ?? $r->COMPLETED_AT ?? null;
                        if (!$p || !$cAt) continue;
                        $pTs = strtotime((string) $p);
                        $cTs = strtotime((string) $cAt);
                        if (!$pTs || !$cTs || $cTs < $pTs) continue;
                        $total += ($cTs - $pTs);
                        $count++;
                    }
                    if ($count > 0) {
                        $avgClosingSeconds = (int) round($total / $count);
                    }
                } catch (\Throwable $e) {
                    $avgClosingSeconds = null;
                }

                $avgClosingDisplay = '-';
                if (is_int($avgClosingSeconds)) {
                    $mins = (int) floor($avgClosingSeconds / 60);
                    if ($mins < 60) {
                        $avgClosingDisplay = $mins . ' min';
                    } elseif ($mins < 60 * 24) {
                        $h = (int) floor($mins / 60);
                        $m = $mins % 60;
                        $avgClosingDisplay = $h . 'h ' . $m . 'm';
                    } else {
                        $d2 = (int) floor($mins / (60 * 24));
                        $remM = $mins % (60 * 24);
                        $h2 = (int) floor($remM / 60);
                        $avgClosingDisplay = $d2 . 'd ' . $h2 . 'h';
                    }
                }

                $postcode = trim((string) ($d->POSTCODE ?? ''));
                $city = trim((string) ($d->CITY ?? ''));
                $location = trim(trim($postcode . ' ' . $city));
                return [
                    // Per requirement: show company column for dealers.
                    'dealer_name' => $company,
                    'location' => $location,
                    'total_leads' => $leads,
                    'closed_count' => $closed,
                    'failed_count' => $failed,
                    'conversion_rate' => round($conversion * 100, 1),
                    'avg_closing_time' => $avgClosingDisplay,
                ];
            })
                // Highest conversion rate first; tie-breakers: closed, leads
                ->sort(function (array $a, array $b) {
                    $c = ($b['conversion_rate'] <=> $a['conversion_rate']);
                    if ($c !== 0) return $c;
                    $c2 = ($b['closed_count'] <=> $a['closed_count']);
                    if ($c2 !== 0) return $c2;
                    return ($b['total_leads'] <=> $a['total_leads']);
                })
                ->values()
                ->all();
        } catch (\Throwable $e) {
            // Schema may differ; keep empty
        }

        // Closed cases (LEAD_ACT STATUS = 'Completed') - week/month/year
        $chartLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $chartData = [12, 19, 15, 22, 18, 24, 20];
        $referralWeekData = [0, 0, 0, 0, 0, 0, 0];
        try {
            $startOfWeek = now()->startOfWeek(\Carbon\Carbon::MONDAY);
            for ($i = 0; $i < 7; $i++) {
                $day = $startOfWeek->copy()->addDays($i)->format('Y-m-d');
                $r = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD_ACT" WHERE CAST("CREATIONDATE" AS DATE) = CAST(? AS DATE) AND UPPER(TRIM("STATUS")) = \'COMPLETED\'',
                    [$day]
                );
                $chartData[$i] = (int) ($r->c ?? $r->C ?? current((array) $r) ?? 0);

                $ro = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD_ACT" WHERE CAST("CREATIONDATE" AS DATE) = CAST(? AS DATE) AND "STATUS" = \'FollowUp\'',
                    [$day]
                );
                $referralWeekData[$i] = (int) ($ro->c ?? $ro->C ?? current((array) $ro) ?? 0);
            }
        } catch (\Throwable $e) {
            // keep default chartData
        }

        $chartMonthLabels = [];
        $chartMonthData = [];
        $referralMonthData = [];
        try {
            $start = now()->startOfMonth();
            $daysInMonth = $start->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $chartMonthLabels[] = (string) $i;
                $day = $start->copy()->day($i)->format('Y-m-d');
                $r = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD_ACT" WHERE CAST("CREATIONDATE" AS DATE) = CAST(? AS DATE) AND UPPER(TRIM("STATUS")) = \'COMPLETED\'',
                    [$day]
                );
                $chartMonthData[] = (int) ($r->c ?? $r->C ?? current((array) $r) ?? 0);

                $ro = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD_ACT" WHERE CAST("CREATIONDATE" AS DATE) = CAST(? AS DATE) AND "STATUS" = \'FollowUp\'',
                    [$day]
                );
                $referralMonthData[] = (int) ($ro->c ?? $ro->C ?? current((array) $ro) ?? 0);
            }
        } catch (\Throwable $e) {
            $chartMonthLabels = ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30'];
            $chartMonthData = array_slice([12, 19, 15, 22, 18, 24, 20, 16, 21, 13, 17, 23, 19, 14, 18, 24, 20, 15, 22, 18, 24, 20, 16, 21, 13, 17, 23, 19, 14, 18], 0, count($chartMonthLabels));
            $referralMonthData = array_fill(0, count($chartMonthLabels), 0);
        }

        $chartYearLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $chartYearData = array_fill(0, 12, 0);
        $referralYearData = array_fill(0, 12, 0);
        try {
            $yearStart = now()->startOfYear();
            for ($m = 0; $m < 12; $m++) {
                $monthStart = $yearStart->copy()->addMonths($m);
                $monthEnd = $monthStart->copy()->endOfMonth();
                $r = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD_ACT" WHERE "CREATIONDATE" >= ? AND "CREATIONDATE" <= ? AND UPPER(TRIM("STATUS")) = \'COMPLETED\'',
                    [$monthStart->format('Y-m-d 00:00:00'), $monthEnd->format('Y-m-d 23:59:59')]
                );
                $chartYearData[$m] = (int) ($r->c ?? $r->C ?? current((array) $r) ?? 0);

                $ro = DB::selectOne(
                    'SELECT COUNT(*) as c FROM "LEAD_ACT" WHERE "CREATIONDATE" >= ? AND "CREATIONDATE" <= ? AND "STATUS" = \'FollowUp\'',
                    [$monthStart->format('Y-m-d 00:00:00'), $monthEnd->format('Y-m-d 23:59:59')]
                );
                $referralYearData[$m] = (int) ($ro->c ?? $ro->C ?? current((array) $ro) ?? 0);
            }
        } catch (\Throwable $e) {
            $chartYearData = [120, 98, 110, 130, 90, 105, 125, 115, 100, 140, 135, 150];
            $referralYearData = array_fill(0, 12, 0);
        }

        return [
            'totalLeads' => $totalLeads,
            'totalClosed' => $totalClosed,
            'activeInquiries' => $activeInquiries,
            'conversionRate' => $conversionRate,
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
        $unassigned = [];
        $assigned = [];
        foreach ($rows as $r) {
            $assignedTo = trim((string) ($r->ASSIGNED_TO ?? ''));
            if ($assignedTo === '') {
                $unassigned[] = $r;
            } else {
                $assigned[] = $r;
            }
        }

        // Default order: incoming = date oldest first; assigned = assign date latest first
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

        // Resolve display names (ALIAS first) from USERS for:
        // - Source (CREATEDBY)
        // - Assigned By (CREATEDBY)
        // - Assigned To (ASSIGNED_TO)
        try {
            $ids = [];
            foreach ($rows as $r) {
                $to = trim((string) ($r->ASSIGNED_TO ?? ''));
                $by = trim((string) ($r->CREATEDBY ?? ''));
                if ($to !== '') $ids[$to] = true;
                if ($by !== '') $ids[$by] = true;
            }
            $ids = array_keys($ids);
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $users = DB::select('SELECT "USERID","SYSTEMROLE","ALIAS","COMPANY","EMAIL" FROM "USERS" WHERE CAST("USERID" AS VARCHAR(50)) IN (' . $placeholders . ')', $ids);
                $userMap = [];
                foreach ($users as $u) {
                    $uid = trim((string) ($u->USERID ?? ''));
                    if ($uid === '') continue;
                    $role = trim((string) ($u->SYSTEMROLE ?? ''));
                    $alias = trim((string) ($u->ALIAS ?? ''));
                    $fallback = trim((string) ($u->COMPANY ?? ''));
                    if ($fallback === '') $fallback = trim((string) ($u->EMAIL ?? ''));
                    if ($fallback === '') $fallback = $uid;

                    if ($role !== '' && $alias !== '') {
                        $label = $role . '- ' . $alias;
                    } elseif ($role !== '') {
                        $label = $role . '- ' . $fallback;
                    } elseif ($alias !== '') {
                        $label = $alias;
                    } else {
                        $label = $fallback;
                    }
                    $userMap[$uid] = $label;
                }
                foreach ($rows as $r) {
                    $to = trim((string) ($r->ASSIGNED_TO ?? ''));
                    $by = trim((string) ($r->CREATEDBY ?? ''));
                    if ($to !== '' && isset($userMap[$to])) $r->ASSIGNED_TO_NAME = $userMap[$to];
                    if ($by !== '' && isset($userMap[$by])) $r->CREATEDBY_NAME = $userMap[$by];
                }
            }
        } catch (\Throwable $e) {
            // fall back to raw ids
        }
        $totalNewInquiries = count($unassigned);
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

        // Dealer list for Assign dropdown (with stats similar to Dealers page)
        $dealers = [];
        try {
            $baseDealers = DB::select(
                'SELECT "USERID","EMAIL","POSTCODE","CITY","ISACTIVE","COMPANY","ALIAS"
                 FROM "USERS"
                 WHERE TRIM("SYSTEMROLE") = \'Dealer\'
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

        return view('admin.inquiries', [
            'unassigned' => $unassigned,
            'assigned' => $assigned,
            'totalNewInquiries' => $totalNewInquiries,
            'productLabels' => $productLabels,
            'dealers' => $dealers,
            'currentPage' => 'inquiries',
        ]);
    }

    public function inquiriesSync(): JsonResponse
    {
        // Reuse the same data as the main inquiries page
        $view = $this->inquiries();
        $data = $view->getData();

        $unassignedHtml = view('admin.partials.inquiries_unassigned_rows', $data)->render();
        $assignedHtml = view('admin.partials.inquiries_assigned_rows', $data)->render();

        return response()->json([
            'unassigned' => $unassignedHtml,
            'assigned' => $assignedHtml,
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

        // Resolve nice names for description (SYSTEMROLE- ALIAS)
        $nameMap = [];
        try {
            $ids = array_values(array_unique(array_filter([$fromUserId, $assignedTo], fn ($v) => trim((string) $v) !== '')));
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $users = DB::select('SELECT "USERID","SYSTEMROLE","ALIAS","COMPANY","EMAIL" FROM "USERS" WHERE CAST("USERID" AS VARCHAR(50)) IN (' . $placeholders . ')', $ids);
                foreach ($users as $u) {
                    $uid = trim((string) ($u->USERID ?? ''));
                    if ($uid === '') continue;
                    $role = trim((string) ($u->SYSTEMROLE ?? ''));
                    $alias = trim((string) ($u->ALIAS ?? ''));
                    $fallback = trim((string) ($u->COMPANY ?? ''));
                    if ($fallback === '') $fallback = trim((string) ($u->EMAIL ?? ''));
                    if ($fallback === '') $fallback = $uid;
                    if ($role !== '' && $alias !== '') $label = $role . '- ' . $alias;
                    elseif ($role !== '') $label = $role . '- ' . $fallback;
                    elseif ($alias !== '') $label = $alias;
                    else $label = $fallback;
                    $nameMap[$uid] = $label;
                }
            }
        } catch (\Throwable $e) {}

        $fromLabel = $fromUserId !== '' ? ($nameMap[$fromUserId] ?? $fromUserId) : 'System';
        $toLabel = $nameMap[$assignedTo] ?? $assignedTo;

        try {
            // Set assigner in session context so the LEAD trigger can use it for LEAD_ACT.USERID (assigned-by, not assigned-to)
            if ($fromUserId !== '') {
                DB::statement(
                    "SELECT RDB\$SET_CONTEXT('USER_SESSION', 'ASSIGNER', ?) FROM RDB\$DATABASE",
                    [$fromUserId]
                );
            }
            DB::update(
                'UPDATE "LEAD" SET "ASSIGNED_TO" = ?, "LASTMODIFIED" = CURRENT_TIMESTAMP WHERE "LEADID" = ?',
                [$assignedTo, $leadId]
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not assign lead: ' . $e->getMessage());
        }

        return redirect()->route('admin.inquiries')->with('success', 'Lead asssigned successfully.');
    }

    public function markInquiryFailed(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'LEADID' => 'required|integer|min:1',
            'DESCRIPTION' => 'required|string|max:4000',
        ]);
        $leadId = (int) $validated['LEADID'];
        $reason = trim((string) ($validated['DESCRIPTION'] ?? ''));
        $userId = trim((string) ($request->session()->get('user_id') ?? ''));

        $latest = DB::selectOne(
            'SELECT FIRST 1 "STATUS" FROM "LEAD_ACT" WHERE "LEADID" = ? ORDER BY "CREATIONDATE" DESC, "LEAD_ACTID" DESC',
            [$leadId]
        );
        $currentStatus = $latest ? strtoupper(trim((string) ($latest->STATUS ?? ''))) : '';
        if (in_array($currentStatus, ['COMPLETED', 'REWARDED', 'FAILED'], true)) {
            return back()->with('error', 'Cannot mark as Failed: lead is already ' . $currentStatus . '.');
        }

        $message = 'Status changed to Failed by ' . ($userId !== '' ? $userId : 'Admin') . '. ' . $reason;
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
            return back()->with('error', 'Could not mark as failed: ' . $e->getMessage());
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

    public function createInquiry(): View
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
        $productInterestedList = [
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
        return view('admin.inquiries-create', [
            'dealers' => $dealers,
            'productInterestedList' => $productInterestedList,
            'currentPage' => 'inquiries',
        ]);
    }

    public function storeInquiry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'COMPANYNAME' => 'required|string|max:255',
            'CONTACTNAME' => 'required|string|max:255',
            'CONTACTNO' => 'required|string|max:100',
            'EMAIL' => 'required|email|max:255',
            'ADDRESS1' => 'nullable|string|max:255',
            'ADDRESS2' => 'nullable|string|max:255',
            'CITY' => 'required|string|max:100',
            'POSTCODE' => 'required|string|max:20',
            'BUSINESSNATURE' => 'required|string|max:255',
            'USERCOUNT' => 'nullable|string|max:50',
            'EXISTINGSOFTWARE' => 'required|string|max:255',
            'DEMOMODE' => 'required|string|in:Zoom,On-site',
            'product_interested' => 'required|array',
            'product_interested.*' => 'integer|in:1,2,3,4,5,6,7,8,9,10,11',
            'DESCRIPTION' => 'nullable|string|max:4000',
            'REFERRALCODE' => 'nullable|string|max:100',
            'ASSIGNED_TO' => 'nullable|string|max:50',
        ]);

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
                    "PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL",
                    "ADDRESS1","ADDRESS2","CITY","POSTCODE","BUSINESSNATURE","USERCOUNT",
                    "EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION","REFERRALCODE",
                    "CURRENTSTATUS","CREATEDAT","CREATEDBY","ASSIGNED_TO","LASTMODIFIED"
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,?,?,CURRENT_TIMESTAMP)',
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
            return back()->withInput($request->only(array_keys($validated)))->with('error', 'Could not save inquiry: ' . $e->getMessage());
        }

        return redirect()->route('admin.inquiries')->with('success', 'Inquiry created.');
    }

    public function dealers(): View
    {
        $rows = DB::select(
            'SELECT "USERID","EMAIL","POSTCODE","CITY","ISACTIVE","COMPANY","ALIAS"
             FROM "USERS"
             WHERE TRIM("SYSTEMROLE") = \'Dealer\'
             ORDER BY "USERID"'
        );

        $leadStats = [];
        try {
            $statsRows = DB::select(
                'SELECT
                    TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50))) AS UID,
                    COUNT(*) AS TOTAL_LEAD,
                    SUM(CASE WHEN "CURRENTSTATUS" = \'Closed\' THEN 1 ELSE 0 END) AS TOTAL_CLOSED,
                    SUM(CASE WHEN UPPER(TRIM("CURRENTSTATUS")) = \'FAILED\' THEN 1 ELSE 0 END) AS TOTAL_FAILED
                 FROM "LEAD"
                 WHERE "ASSIGNED_TO" IS NOT NULL AND TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50))) <> \'\'
                 GROUP BY TRIM(CAST("ASSIGNED_TO" AS VARCHAR(50)))'
            );
            foreach ($statsRows as $sr) {
                $uid = trim((string) ($sr->UID ?? $sr->uid ?? ''));
                if ($uid === '') continue;
                $totalLead = (int) ($sr->TOTAL_LEAD ?? $sr->total_lead ?? 0);
                $totalClosed = (int) ($sr->TOTAL_CLOSED ?? $sr->total_closed ?? 0);
                $totalFailed = (int) ($sr->TOTAL_FAILED ?? $sr->total_failed ?? 0);
                $leadStats[$uid] = [
                    'totalLead' => $totalLead,
                    'totalClosed' => $totalClosed,
                    'totalFailed' => $totalFailed,
                ];
            }
        } catch (\Throwable $e) {
            // leave stats empty
        }

        $items = array_map(function ($r) use ($leadStats) {
            $uid = trim((string) ($r->USERID ?? ''));
            $totalLead = $leadStats[$uid]['totalLead'] ?? 0;
            $totalClosed = $leadStats[$uid]['totalClosed'] ?? 0;
            $totalFailed = $leadStats[$uid]['totalFailed'] ?? 0;
            $conversion = $totalLead > 0 ? ($totalClosed / $totalLead) * 100 : 0;
            $r->TOTAL_LEAD = $totalLead;
            $r->TOTAL_CLOSED = $totalClosed;
            $r->TOTAL_FAILED = $totalFailed;
            $r->CONVERSION_RATE = $conversion;
            return $r;
        }, $rows);

        return view('admin.dealers', ['items' => $items, 'currentPage' => 'dealers']);
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
            'Failed' => 0,
        ];
        foreach ($leadStatusRows as $row) {
            $key = (string) $get($row, 'status');
            if (isset($leadStatus[$key])) {
                $leadStatus[$key] = (int) $get($row, 'c');
            }
        }

        // Unassigned leads: match LEAD status \"Open\"
        $unassignedCount = $leadStatus['Open'] ?? 0;

        $pendingActs = DB::select(
            'SELECT "STATUS" AS status, COUNT(*) AS c FROM "LEAD_ACT" GROUP BY "STATUS"'
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
            $key = (string) $get($row, 'status');
            if ($key === 'Rewarded') {
                $key = 'reward';
            }
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
            $key = (string) $get($row, 'status');
            if ($key === 'Rewarded') {
                $key = 'reward';
            }
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
            $pidRaw = $get($row, 'product_id');
            $pidStr = trim((string) $pidRaw);
            $pidInt = ctype_digit($pidStr) ? (int) $pidStr : null;
            $productConversion[] = [
                'label' => ($pidInt !== null && isset($productLabels[$pidInt]))
                    ? $productLabels[$pidInt]
                    : ($pidStr !== '' ? $pidStr : 'Unknown'),
                'count' => (int) $get($row, 'c'),
            ];
        }

        return view('admin.reports', [
            'currentPage' => 'reports',
            'leadStatus' => $leadStatus,
            'unassignedLeads' => (int) $unassignedCount,
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
