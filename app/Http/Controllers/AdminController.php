<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    private function dashboardData(): array
    {
        $inquiries = DB::select('SELECT COUNT(*) as c FROM "Customer_Inquiries"');
        $deals = DB::select('SELECT COUNT(*) as c FROM "Deals_Submissions"');
        $dealers = DB::select('SELECT COUNT(*) as c FROM "Dealers"');
        return [
            'totalInquiries' => $inquiries[0]->c ?? 0,
            'totalDeals' => $deals[0]->c ?? 0,
            'totalDealers' => $dealers[0]->c ?? 0,
        ];
    }

    public function dashboard(): View
    {
        return view('admin.dashboard', array_merge($this->dashboardData(), ['currentPage' => 'dashboard']));
    }

    public function inquiries(): View
    {
        $rows = DB::select('SELECT "CustomerInquiryID","ProductID","CompanyName","ContactName","ContactNo","EmailAddress","City","IsResolved","SubmittedAt" FROM "Customer_Inquiries" ORDER BY "CustomerInquiryID" DESC LIMIT 100');
        return view('admin.inquiries', ['items' => $rows, 'currentPage' => 'inquiries']);
    }

    public function dealers(): View
    {
        $rows = DB::select('SELECT "DealerID","UserID","DealerName","DealerCode","BankName","JoinDate" FROM "Dealers" ORDER BY "DealerID"');
        return view('admin.dealers', ['items' => $rows, 'currentPage' => 'dealers']);
    }

    public function rewards(): View
    {
        $rows = DB::select('SELECT "ReferrerPayoutID","DealsSubmissionID","DealerID","ReferrerID","Status","DateGenerated","DatePaid" FROM "Referrer_Payouts" ORDER BY "ReferrerPayoutID" DESC LIMIT 100');
        return view('admin.rewards', ['items' => $rows, 'currentPage' => 'rewards']);
    }

    public function reports(): View
    {
        return view('admin.reports', ['currentPage' => 'reports']);
    }

    public function history(): View
    {
        $rows = DB::select('SELECT "DealHistoryStatusID","DealsSubmissionID","PreviousStatus","NewStatus","ChangedByID","ChangeDate" FROM "Deal_Status_History" ORDER BY "DealHistoryStatusID" DESC LIMIT 100');
        return view('admin.history', ['items' => $rows, 'currentPage' => 'history']);
    }

    public function fulldatabase(): View
    {
        $tables = [
            'products' => DB::select('SELECT "ProductID", "ProductName", "Category", "BasePriceRM" FROM "Products" ORDER BY "ProductID"'),
            'clients_leads' => DB::select('SELECT "ClientsLeadID","CompanyName","ContactPerson","Email","PhoneNo","CityState","Industry","CreatedDate" FROM "Clients_Leads" ORDER BY "ClientsLeadID"'),
            'referrers' => DB::select('SELECT "ReferrerID","FullName","PhoneNo","BankName","BankAccountNo" FROM "Referrers" ORDER BY "ReferrerID"'),
            'users' => DB::select('SELECT "UserID","Email","SystemRole","IsActive","LastLogin" FROM "Users" ORDER BY "UserID"'),
            'admins' => DB::select('SELECT "AdminID","UserID","AdminName" FROM "Admins" ORDER BY "AdminID"'),
            'dealers' => DB::select('SELECT "DealerID","UserID","DealerName","DealerCode","BankName","JoinDate" FROM "Dealers" ORDER BY "DealerID"'),
            'customer_inquiries' => DB::select('SELECT "CustomerInquiryID","ProductID","CompanyName","ContactName","IsResolved","SubmittedAt" FROM "Customer_Inquiries" ORDER BY "CustomerInquiryID"'),
            'admin_interventions' => DB::select('SELECT "AdminInterventionID","DealerID","AdminID","ActionTaken","DateLogged" FROM "Admin_Interventions" ORDER BY "AdminInterventionID"'),
            'deals_submissions' => DB::select('SELECT "DealsSubmissionID","DealerID","ClientsLeadID","PipelineStatus","ExpectedTotalRevenueRM","DateAssigned" FROM "Deals_Submissions" ORDER BY "DealsSubmissionID"'),
            'deal_items' => DB::select('SELECT "DealItemID","DealSubmissionID","ProductID","Quantity","QuotedPriceRM" FROM "Deal_Items" ORDER BY "DealItemID"'),
            'referrer_payouts' => DB::select('SELECT "ReferrerPayoutID","DealsSubmissionID","DealerID","ReferrerID","Status","DateGenerated" FROM "Referrer_Payouts" ORDER BY "ReferrerPayoutID"'),
            'deal_status_history' => DB::select('SELECT "DealHistoryStatusID","DealsSubmissionID","PreviousStatus","NewStatus","ChangeDate" FROM "Deal_Status_History" ORDER BY "DealHistoryStatusID"'),
        ];
        return view('admin.fulldatabase', ['tables' => $tables, 'currentPage' => 'fulldatabase']);
    }
}
