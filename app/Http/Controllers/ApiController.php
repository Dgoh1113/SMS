<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    public function status(): JsonResponse
    {
        return response()->json(['message' => 'API is running and PostgreSQL is connected!']);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $row = DB::selectOne(
            'SELECT "UserID", "PasswordHash", "SystemRole", "IsActive" FROM "Users" WHERE "Email" = ?',
            [$validated['email']]
        );

        if (!$row) {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }

        if (!$row->IsActive) {
            return response()->json(['error' => 'Account is deactivated'], 403);
        }

        $stored = (string) ($row->PasswordHash ?? '');
        $looksHashed = str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$argon2');
        $ok = $looksHashed ? Hash::check($validated['password'], $stored) : hash_equals($stored, $validated['password']);

        if (!$ok) {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }

        if (!$looksHashed) {
            DB::update(
                'UPDATE "Users" SET "PasswordHash" = ? WHERE "UserID" = ?',
                [Hash::make($validated['password']), $row->UserID]
            );
        }

        DB::update('UPDATE "Users" SET "LastLogin" = NOW() WHERE "UserID" = ?', [$row->UserID]);

        $role = $row->SystemRole === 'Admin' ? 'admin' : 'dealer';

        return response()->json([
            'message' => 'Login successful',
            'userID' => $row->UserID,
            'email' => $validated['email'],
            'role' => $role,
        ]);
    }

    public function debugTables(): JsonResponse
    {
        $rows = DB::select("SELECT table_schema, table_name FROM information_schema.tables WHERE table_schema NOT IN ('pg_catalog','information_schema') ORDER BY table_schema, table_name");
        $tables = array_map(fn ($r) => ['schema' => $r->table_schema, 'table' => $r->table_name], $rows);
        return response()->json($tables);
    }

    public function debugColumns(string $table): JsonResponse
    {
        $rows = DB::select('SELECT column_name, data_type FROM information_schema.columns WHERE table_name = ? ORDER BY ordinal_position', [$table]);
        $cols = array_map(fn ($r) => ['column' => $r->column_name, 'type' => $r->data_type], $rows);
        return response()->json($cols);
    }

    // ── Products ──
    public function productsIndex(): JsonResponse
    {
        $rows = DB::select('SELECT "ProductID", "ProductName", "Category", "BasePriceRM" FROM "Products" ORDER BY "ProductID"');
        $items = array_map(fn ($r) => (array) $r, $rows);
        return response()->json($items);
    }

    public function productsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ProductName' => 'required|string',
            'Category' => 'required|string',
            'BasePriceRM' => 'required|numeric',
        ]);
        $id = DB::selectOne(
            'INSERT INTO "Products" ("ProductName","Category","BasePriceRM") VALUES (?,?,?) RETURNING "ProductID"',
            [$validated['ProductName'], $validated['Category'], $validated['BasePriceRM']]
        )->ProductID;
        return response()->json([
            'ProductID' => $id,
            'ProductName' => $validated['ProductName'],
            'Category' => $validated['Category'],
            'BasePriceRM' => $validated['BasePriceRM'],
        ], 201);
    }

    // ── Clients / Leads ──
    public function clientsLeadsIndex(): JsonResponse
    {
        $rows = DB::select('SELECT "ClientsLeadID","CompanyName","ContactPerson","Email","PhoneNo","CityState","Industry","CreatedDate" FROM "Clients_Leads" ORDER BY "ClientsLeadID"');
        $items = array_map(function ($r) {
            $a = (array) $r;
            if (isset($a['CreatedDate'])) {
                $a['CreatedDate'] = date('Y-m-d', strtotime($a['CreatedDate']));
            }
            return $a;
        }, $rows);
        return response()->json($items);
    }

    public function clientsLeadsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'CompanyName' => 'required|string',
            'ContactPerson' => 'required|string',
            'Email' => 'required|string',
            'PhoneNo' => 'required|string',
            'CityState' => 'nullable|string',
            'Industry' => 'nullable|string',
        ]);
        $row = DB::selectOne(
            'INSERT INTO "Clients_Leads" ("CompanyName","ContactPerson","Email","PhoneNo","CityState","Industry","CreatedDate") VALUES (?,?,?,?,?,?,CURRENT_DATE) RETURNING "ClientsLeadID","CreatedDate"',
            [$validated['CompanyName'], $validated['ContactPerson'], $validated['Email'], $validated['PhoneNo'], $validated['CityState'] ?? '', $validated['Industry'] ?? '']
        );
        return response()->json([
            'ClientsLeadID' => $row->ClientsLeadID,
            'CompanyName' => $validated['CompanyName'],
            'ContactPerson' => $validated['ContactPerson'],
            'Email' => $validated['Email'],
            'PhoneNo' => $validated['PhoneNo'],
            'CityState' => $validated['CityState'] ?? '',
            'Industry' => $validated['Industry'] ?? '',
            'CreatedDate' => date('Y-m-d', strtotime($row->CreatedDate)),
        ], 201);
    }

    // ── Referrers ──
    public function referrersIndex(): JsonResponse
    {
        $rows = DB::select('SELECT "ReferrerID","FullName","PhoneNo","BankName","BankAccountNo" FROM "Referrers" ORDER BY "ReferrerID"');
        return response()->json(array_map(fn ($r) => (array) $r, $rows));
    }

    public function referrersStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'FullName' => 'required|string',
            'PhoneNo' => 'required|string',
            'BankName' => 'nullable|string',
            'BankAccountNo' => 'nullable|string',
        ]);
        $id = DB::selectOne(
            'INSERT INTO "Referrers" ("FullName","PhoneNo","BankName","BankAccountNo") VALUES (?,?,?,?) RETURNING "ReferrerID"',
            [$validated['FullName'], $validated['PhoneNo'], $validated['BankName'] ?? '', $validated['BankAccountNo'] ?? '']
        )->ReferrerID;
        return response()->json([
            'ReferrerID' => $id,
            'FullName' => $validated['FullName'],
            'PhoneNo' => $validated['PhoneNo'],
            'BankName' => $validated['BankName'] ?? '',
            'BankAccountNo' => $validated['BankAccountNo'] ?? '',
        ], 201);
    }

    // ── Users ──
    public function usersIndex(): JsonResponse
    {
        $rows = DB::select('SELECT "UserID","Email","SystemRole","IsActive","LastLogin","CreatedAt" FROM "Users" ORDER BY "UserID"');
        $items = array_map(function ($r) {
            $a = (array) $r;
            if (!empty($a['LastLogin'])) {
                $a['LastLogin'] = date('Y-m-d H:i:s', strtotime($a['LastLogin']));
            }
            if (!empty($a['CreatedAt'])) {
                $a['CreatedAt'] = date('Y-m-d H:i:s', strtotime($a['CreatedAt']));
            }
            return $a;
        }, $rows);
        return response()->json($items);
    }

    public function usersStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Email' => 'required|string',
            'PasswordHash' => 'required|string',
            'SystemRole' => 'required|string',
        ]);
        $row = DB::selectOne(
            'INSERT INTO "Users" ("Email","PasswordHash","SystemRole","IsActive","LastLogin","CreatedAt") VALUES (?,?,?,true,NOW(),NOW()) RETURNING "UserID","CreatedAt"',
            [$validated['Email'], $validated['PasswordHash'], $validated['SystemRole']]
        );
        return response()->json([
            'UserID' => $row->UserID,
            'Email' => $validated['Email'],
            'SystemRole' => $validated['SystemRole'],
            'IsActive' => true,
            'CreatedAt' => date('Y-m-d H:i:s', strtotime($row->CreatedAt)),
        ], 201);
    }

    // ── Admins ──
    public function adminsIndex(): JsonResponse
    {
        $rows = DB::select('SELECT "AdminID","UserID","AdminName" FROM "Admins" ORDER BY "AdminID"');
        return response()->json(array_map(fn ($r) => (array) $r, $rows));
    }

    public function adminsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'UserID' => 'required|integer',
            'AdminName' => 'required|string',
        ]);
        $id = DB::selectOne(
            'INSERT INTO "Admins" ("UserID","AdminName") VALUES (?,?) RETURNING "AdminID"',
            [$validated['UserID'], $validated['AdminName']]
        )->AdminID;
        return response()->json([
            'AdminID' => $id,
            'UserID' => $validated['UserID'],
            'AdminName' => $validated['AdminName'],
        ], 201);
    }

    // ── Dealers ──
    public function dealersIndex(): JsonResponse
    {
        $rows = DB::select('SELECT "DealerID","UserID","DealerName","DealerCode","BankName","BankAccountNo","JoinDate" FROM "Dealers" ORDER BY "DealerID"');
        $items = array_map(function ($r) {
            $a = (array) $r;
            if (!empty($a['JoinDate'])) {
                $a['JoinDate'] = date('Y-m-d', strtotime($a['JoinDate']));
            }
            return $a;
        }, $rows);
        return response()->json($items);
    }

    public function dealersStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'UserID' => 'required|integer',
            'DealerName' => 'required|string',
            'DealerCode' => 'required|string',
            'BankName' => 'nullable|string',
            'BankAccountNo' => 'nullable|string',
        ]);
        $row = DB::selectOne(
            'INSERT INTO "Dealers" ("UserID","DealerName","DealerCode","BankName","BankAccountNo","JoinDate") VALUES (?,?,?,?,?,CURRENT_DATE) RETURNING "DealerID","JoinDate"',
            [$validated['UserID'], $validated['DealerName'], $validated['DealerCode'], $validated['BankName'] ?? '', $validated['BankAccountNo'] ?? '']
        );
        return response()->json([
            'DealerID' => $row->DealerID,
            'UserID' => $validated['UserID'],
            'DealerName' => $validated['DealerName'],
            'DealerCode' => $validated['DealerCode'],
            'BankName' => $validated['BankName'] ?? '',
            'BankAccountNo' => $validated['BankAccountNo'] ?? '',
            'JoinDate' => date('Y-m-d', strtotime($row->JoinDate)),
        ], 201);
    }

    // ── Customer Inquiries ──
    public function customerInquiriesIndex(): JsonResponse
    {
        $rows = DB::select('SELECT "CustomerInquiryID","ProductID","CompanyName","ContactName","ContactNo","EmailAddress","City","Postcode","BusinessNature","ConcurrentUsers","Version","ExistingAccountingSoftware","SoftwareDemo","Message","IsResolved","SubmittedAt" FROM "Customer_Inquiries" ORDER BY "CustomerInquiryID"');
        $items = array_map(function ($r) {
            $a = (array) $r;
            if (!empty($a['SubmittedAt'])) {
                $a['SubmittedAt'] = date('Y-m-d\TH:i:s', strtotime($a['SubmittedAt']));
            } else {
                $a['SubmittedAt'] = null;
            }
            return $a;
        }, $rows);
        return response()->json($items);
    }

    public function customerInquiriesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ProductID' => 'required|integer',
            'CompanyName' => 'required|string',
            'ContactName' => 'required|string',
            'ContactNo' => 'required|string',
            'EmailAddress' => 'required|string',
            'City' => 'nullable|string',
            'Postcode' => 'nullable|string',
            'BusinessNature' => 'nullable|string',
            'ConcurrentUsers' => 'nullable|integer',
            'Version' => 'nullable|string',
            'ExistingAccountingSoftware' => 'nullable|string',
            'SoftwareDemo' => 'nullable|string',
            'Message' => 'nullable|string',
        ]);
        $id = DB::selectOne(
            'INSERT INTO "Customer_Inquiries" ("ProductID","CompanyName","ContactName","ContactNo","EmailAddress","City","Postcode","BusinessNature","ConcurrentUsers","Version","ExistingAccountingSoftware","SoftwareDemo","Message","IsResolved") VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,false) RETURNING "CustomerInquiryID"',
            [
                $validated['ProductID'], $validated['CompanyName'], $validated['ContactName'], $validated['ContactNo'],
                $validated['EmailAddress'], $validated['City'] ?? '', $validated['Postcode'] ?? '', $validated['BusinessNature'] ?? '',
                $validated['ConcurrentUsers'] ?? 0, $validated['Version'] ?? '', $validated['ExistingAccountingSoftware'] ?? '',
                $validated['SoftwareDemo'] ?? '', $validated['Message'] ?? '',
            ]
        )->CustomerInquiryID;
        return response()->json([
            'CustomerInquiryID' => $id,
            'ProductID' => $validated['ProductID'],
            'CompanyName' => $validated['CompanyName'],
            'IsResolved' => false,
        ], 201);
    }

    // ── Admin Interventions ──
    public function adminInterventionsIndex(): JsonResponse
    {
        $rows = DB::select('SELECT "AdminInterventionID","DealerID","AdminID","ActionTaken","AdminNotes","DateLogged" FROM "Admin_Interventions" ORDER BY "AdminInterventionID"');
        $items = array_map(function ($r) {
            $a = (array) $r;
            if (!empty($a['DateLogged'])) {
                $a['DateLogged'] = date('Y-m-d H:i:s', strtotime($a['DateLogged']));
            }
            return $a;
        }, $rows);
        return response()->json($items);
    }

    public function adminInterventionsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'DealerID' => 'required|integer',
            'AdminID' => 'required|integer',
            'ActionTaken' => 'required|string',
            'AdminNotes' => 'nullable|string',
        ]);
        $row = DB::selectOne(
            'INSERT INTO "Admin_Interventions" ("DealerID","AdminID","ActionTaken","AdminNotes","DateLogged") VALUES (?,?,?,?,NOW()) RETURNING "AdminInterventionID","DateLogged"',
            [$validated['DealerID'], $validated['AdminID'], $validated['ActionTaken'], $validated['AdminNotes'] ?? '']
        );
        return response()->json([
            'AdminInterventionID' => $row->AdminInterventionID,
            'DealerID' => $validated['DealerID'],
            'AdminID' => $validated['AdminID'],
            'ActionTaken' => $validated['ActionTaken'],
            'AdminNotes' => $validated['AdminNotes'] ?? '',
            'DateLogged' => date('Y-m-d H:i:s', strtotime($row->DateLogged)),
        ], 201);
    }

    // ── Deals Submissions ──
    public function dealsSubmissionsIndex(): JsonResponse
    {
        $rows = DB::select('SELECT "DealsSubmissionID","DealerID","ClientsLeadID","PipelineStatus","IsDemoCompleted","DemoDate","RejectionCount","LastRejectionReason","ExpectedTotalRevenueRM","DateAssigned","LastUpdatedDate" FROM "Deals_Submissions" ORDER BY "DealsSubmissionID"');
        $items = array_map(function ($r) {
            $a = (array) $r;
            if (!empty($a['DemoDate'])) {
                $a['DemoDate'] = date('Y-m-d', strtotime($a['DemoDate']));
            } else {
                $a['DemoDate'] = null;
            }
            if (!empty($a['DateAssigned'])) {
                $a['DateAssigned'] = date('Y-m-d', strtotime($a['DateAssigned']));
            }
            if (!empty($a['LastUpdatedDate'])) {
                $a['LastUpdatedDate'] = date('Y-m-d', strtotime($a['LastUpdatedDate']));
            }
            return $a;
        }, $rows);
        return response()->json($items);
    }

    public function dealsSubmissionsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'DealerID' => 'required|integer',
            'ClientsLeadID' => 'required|integer',
            'PipelineStatus' => 'required|string',
            'ExpectedTotalRevenueRM' => 'nullable|numeric',
        ]);
        $row = DB::selectOne(
            'INSERT INTO "Deals_Submissions" ("DealerID","ClientsLeadID","PipelineStatus","IsDemoCompleted","RejectionCount","ExpectedTotalRevenueRM","DateAssigned","LastUpdatedDate") VALUES (?,?,?,false,0,?,CURRENT_DATE,CURRENT_DATE) RETURNING "DealsSubmissionID","DateAssigned"',
            [$validated['DealerID'], $validated['ClientsLeadID'], $validated['PipelineStatus'], $validated['ExpectedTotalRevenueRM'] ?? 0]
        );
        return response()->json([
            'DealsSubmissionID' => $row->DealsSubmissionID,
            'DealerID' => $validated['DealerID'],
            'ClientsLeadID' => $validated['ClientsLeadID'],
            'PipelineStatus' => $validated['PipelineStatus'],
            'ExpectedTotalRevenueRM' => $validated['ExpectedTotalRevenueRM'] ?? 0,
            'DateAssigned' => date('Y-m-d', strtotime($row->DateAssigned)),
        ], 201);
    }

    // ── Deal Items ──
    public function dealItemsIndex(): JsonResponse
    {
        $rows = DB::select('SELECT "DealItemID","DealSubmissionID","ProductID","Quantity","QuotedPriceRM" FROM "Deal_Items" ORDER BY "DealItemID"');
        return response()->json(array_map(fn ($r) => (array) $r, $rows));
    }

    public function dealItemsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'DealSubmissionID' => 'required|integer',
            'ProductID' => 'required|integer',
            'Quantity' => 'required|integer',
            'QuotedPriceRM' => 'required|numeric',
        ]);
        $id = DB::selectOne(
            'INSERT INTO "Deal_Items" ("DealSubmissionID","ProductID","Quantity","QuotedPriceRM") VALUES (?,?,?,?) RETURNING "DealItemID"',
            [$validated['DealSubmissionID'], $validated['ProductID'], $validated['Quantity'], $validated['QuotedPriceRM']]
        )->DealItemID;
        return response()->json([
            'DealItemID' => $id,
            'DealSubmissionID' => $validated['DealSubmissionID'],
            'ProductID' => $validated['ProductID'],
            'Quantity' => $validated['Quantity'],
            'QuotedPriceRM' => $validated['QuotedPriceRM'],
        ], 201);
    }

    // ── Referrer Payouts ──
    public function referrerPayoutsIndex(): JsonResponse
    {
        $rows = DB::select('SELECT "ReferrerPayoutID","DealsSubmissionID","DealerID","ReferrerID","Status","DateGenerated","DatePaid" FROM "Referrer_Payouts" ORDER BY "ReferrerPayoutID"');
        $items = array_map(function ($r) {
            $a = (array) $r;
            if (!empty($a['DateGenerated'])) {
                $a['DateGenerated'] = date('Y-m-d', strtotime($a['DateGenerated']));
            }
            if (!empty($a['DatePaid'])) {
                $a['DatePaid'] = date('Y-m-d', strtotime($a['DatePaid']));
            } else {
                $a['DatePaid'] = null;
            }
            return $a;
        }, $rows);
        return response()->json($items);
    }

    public function referrerPayoutsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'DealsSubmissionID' => 'required|integer',
            'DealerID' => 'required|integer',
            'ReferrerID' => 'required|integer',
            'Status' => 'required|string',
        ]);
        $row = DB::selectOne(
            'INSERT INTO "Referrer_Payouts" ("DealsSubmissionID","DealerID","ReferrerID","Status","DateGenerated") VALUES (?,?,?,?,CURRENT_DATE) RETURNING "ReferrerPayoutID","DateGenerated"',
            [$validated['DealsSubmissionID'], $validated['DealerID'], $validated['ReferrerID'], $validated['Status']]
        );
        return response()->json([
            'ReferrerPayoutID' => $row->ReferrerPayoutID,
            'DealsSubmissionID' => $validated['DealsSubmissionID'],
            'DealerID' => $validated['DealerID'],
            'ReferrerID' => $validated['ReferrerID'],
            'Status' => $validated['Status'],
            'DateGenerated' => date('Y-m-d', strtotime($row->DateGenerated)),
        ], 201);
    }

    // ── Deal Status History ──
    public function dealStatusHistoryIndex(): JsonResponse
    {
        $rows = DB::select('SELECT "DealHistoryStatusID","DealsSubmissionID","PreviousStatus","NewStatus","ChangedByID","ChangeDate" FROM "Deal_Status_History" ORDER BY "DealHistoryStatusID"');
        $items = array_map(function ($r) {
            $a = (array) $r;
            if (!empty($a['ChangeDate'])) {
                $a['ChangeDate'] = date('Y-m-d H:i:s', strtotime($a['ChangeDate']));
            }
            return $a;
        }, $rows);
        return response()->json($items);
    }

    public function dealStatusHistoryStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'DealsSubmissionID' => 'required|integer',
            'PreviousStatus' => 'required|string',
            'NewStatus' => 'required|string',
            'ChangedByID' => 'required|integer',
        ]);
        $row = DB::selectOne(
            'INSERT INTO "Deal_Status_History" ("DealsSubmissionID","PreviousStatus","NewStatus","ChangedByID","ChangeDate") VALUES (?,?,?,?,NOW()) RETURNING "DealHistoryStatusID","ChangeDate"',
            [$validated['DealsSubmissionID'], $validated['PreviousStatus'], $validated['NewStatus'], $validated['ChangedByID']]
        );
        return response()->json([
            'DealHistoryStatusID' => $row->DealHistoryStatusID,
            'DealsSubmissionID' => $validated['DealsSubmissionID'],
            'PreviousStatus' => $validated['PreviousStatus'],
            'NewStatus' => $validated['NewStatus'],
            'ChangedByID' => $validated['ChangedByID'],
            'ChangeDate' => date('Y-m-d H:i:s', strtotime($row->ChangeDate)),
        ], 201);
    }
}
