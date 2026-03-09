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
        $deals = [];
        if ($dealerId) {
            $deals = DB::select('SELECT "DealsSubmissionID","ClientsLeadID","PipelineStatus","ExpectedTotalRevenueRM","DateAssigned" FROM "Deals_Submissions" WHERE "DealerID" = ? ORDER BY "DealsSubmissionID" DESC LIMIT 20', [$dealerId]);
        }
        return view('dealer.dashboard', ['deals' => $deals, 'currentPage' => 'dashboard']);
    }

    public function demo(Request $request): View
    {
        $dealerId = $request->session()->get('user_id');
        $deals = [];
        if ($dealerId) {
            $deals = DB::select('SELECT "DealsSubmissionID","ClientsLeadID","PipelineStatus","IsDemoCompleted","DemoDate" FROM "Deals_Submissions" WHERE "DealerID" = ? ORDER BY "DealsSubmissionID" DESC', [$dealerId]);
        }
        return view('dealer.demo', ['deals' => $deals, 'currentPage' => 'demo']);
    }
}
