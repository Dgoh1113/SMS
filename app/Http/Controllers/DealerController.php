<?php

namespace App\Http\Controllers;

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
            'demosThisWeek' => 0,
            'demosTrend' => '+5',
            'pendingFollowups' => 0,
        ];
        $upcomingDemos = [];
        $highPriorityFollowups = [];

        if ($dealerId) {
            $leads = DB::select(
                'SELECT FIRST 50
                    "LEADID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL","CITY","CURRENTSTATUS","CREATEDAT","LASTMODIFIED","DEMOMODE"
                FROM "LEAD"
                WHERE "ASSIGNED_TO" = ?
                ORDER BY "LEADID" DESC',
                [$dealerId]
            );

            $activeCountRow = DB::selectOne(
                'SELECT COUNT(*) AS "CNT" FROM "LEAD"
                WHERE "ASSIGNED_TO" = ? AND UPPER(TRIM(COALESCE("CURRENTSTATUS", \'\'))) = ?',
                [$dealerId, 'ONGOING']
            );
            $activeInquiriesCount = (int) ($activeCountRow->CNT ?? 0);
            $totalAssignedCount = count($leads);

            $closedCountRow = DB::selectOne(
                'SELECT COUNT(*) AS "CNT" FROM "LEAD"
                WHERE "ASSIGNED_TO" = ? AND UPPER(TRIM(COALESCE("CURRENTSTATUS", \'\'))) = ?',
                [$dealerId, 'CLOSED']
            );
            $closedCount = (int) ($closedCountRow->CNT ?? 0);
            $conversion = $totalAssignedCount > 0 ? round(($closedCount / $totalAssignedCount) * 100, 1) : 0;

            $weekStart = date('Y-m-d', strtotime('monday this week'));
            $weekEnd = date('Y-m-d 23:59:59', strtotime('sunday this week'));
            $demosRow = DB::selectOne(
                'SELECT COUNT(*) AS "CNT" FROM "LEAD_ACT" la
                JOIN "LEAD" l ON l."LEADID" = la."LEADID"
                WHERE l."ASSIGNED_TO" = ?
                AND UPPER(TRIM(COALESCE(la."STATUS", \'\'))) = ?
                AND la."CREATIONDATE" >= ? AND la."CREATIONDATE" <= ?',
                [$dealerId, 'DEMO', $weekStart, $weekEnd]
            );
            $demosThisWeek = (int) ($demosRow->CNT ?? 0);

            $metrics = [
                'activeInquiries' => $activeInquiriesCount,
                'activeInquiriesTrend' => '+12%',
                'conversionRate' => $conversion ?: '18.5',
                'conversionTrend' => '-2%',
                'demosThisWeek' => $demosThisWeek,
                'demosTrend' => '+5',
                'pendingFollowups' => min(8, max(0, $totalAssignedCount - $closedCount)) ?: 8,
            ];

            $upcomingDemosRows = DB::select(
                'SELECT FIRST 50 la."LEAD_ACTID", la."LEADID", la."CREATIONDATE", la."SUBJECT", l."CONTACTNAME"
                FROM "LEAD_ACT" la
                JOIN "LEAD" l ON l."LEADID" = la."LEADID"
                WHERE l."ASSIGNED_TO" = ?
                AND UPPER(TRIM(COALESCE(la."STATUS", \'\'))) = ?
                ORDER BY la."CREATIONDATE" DESC',
                [$dealerId, 'DEMO']
            );

            $upcomingDemos = collect($upcomingDemosRows)->take(3)->map(function ($r) {
                $mod = $r->CREATIONDATE ? strtotime($r->CREATIONDATE) : time();
                $name = trim($r->CONTACTNAME ?? '');
                $contact = $name ? 'Ms/Mr ' . explode(' ', $name)[0] : '—';
                return (object) [
                    'leadId' => $r->LEADID,
                    'day' => strtoupper(date('M', $mod)),
                    'dateNum' => date('d', $mod),
                    'title' => trim($r->SUBJECT ?? '') ?: 'SQL Demo',
                    'time' => date('g:i A', $mod),
                    'contact' => $contact,
                ];
            })->values()->all();

            if (count($upcomingDemos) < 2) {
                $upcomingDemos = [
                    (object) ['leadId' => 1, 'day' => 'OCT', 'dateNum' => '24', 'title' => 'SQL Payroll Demo', 'time' => '10:30 AM', 'contact' => 'Ms Ng'],
                    (object) ['leadId' => 2, 'day' => 'OCT', 'dateNum' => '24', 'title' => 'SQL Account Demo', 'time' => '2:00 PM', 'contact' => 'Mr Lim'],
                ];
            }

            $highPriorityFollowups = collect($leads)->take(2)->map(function ($l, $i) {
                $isOverdue = $i === 0;
                return (object) [
                    'leadId' => $l->LEADID,
                    'status' => $isOverdue ? 'OVERDUE' : 'DUE SOON',
                    'time' => $isOverdue ? '2h late' : 'In 45m',
                    'inquiryId' => 'LX-' . $l->LEADID,
                    'contact' => $l->CONTACTNAME ? 'Ms/Mr ' . explode(' ', $l->CONTACTNAME)[0] : '—',
                    'product' => $l->COMPANYNAME ?: 'SQL Account + Stock',
                ];
            })->values()->all();

            if (count($highPriorityFollowups) < 2) {
                $highPriorityFollowups = [
                    (object) ['leadId' => 1234, 'status' => 'OVERDUE', 'time' => '2h late', 'inquiryId' => 'LX-1234', 'contact' => 'Ms Wong', 'product' => 'SQL Account + Stock'],
                    (object) ['leadId' => 6789, 'status' => 'DUE SOON', 'time' => 'In 45m', 'inquiryId' => 'LX-6789', 'contact' => 'Ms Sarah', 'product' => 'SQL Payroll (50 staffs)'],
                ];
            }
        }

        return view('dealer.dashboard', [
            'leads' => $leads,
            'metrics' => $metrics,
            'upcomingDemos' => $upcomingDemos,
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
                'SELECT FIRST 100
                    "LEADID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL","CITY","CURRENTSTATUS","CREATEDAT","LASTMODIFIED"
                FROM "LEAD"
                WHERE "ASSIGNED_TO" = ?
                ORDER BY "LEADID" DESC',
                [$dealerId]
            );
        }
        return view('dealer.inquiries', ['leads' => $leads, 'currentPage' => 'inquiries']);
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
        return view('dealer.reports', ['currentPage' => 'reports']);
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
