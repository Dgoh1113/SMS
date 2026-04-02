<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function status(): JsonResponse
    {
        return response()->json(['message' => 'API is running and Firebird is connected!']);
    }

    public function login(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'API login is not available. Use the web app passkey flow instead.',
        ], 410);
    }

    public function leadsIndex(): JsonResponse
    {
        $rows = DB::select(
            'SELECT FIRST 200
                "LEADID","PRODUCTID","COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL","ADDRESS1","ADDRESS2",
                "CITY","POSTCODE","BUSINESSNATURE","USERCOUNT","EXISTINGSOFTWARE","DEMOMODE","DESCRIPTION",
                "REFERRALCODE","CURRENTSTATUS","CREATEDAT","CREATEDBY","ASSIGNED_TO","LASTMODIFIED"
            FROM "LEAD"
            ORDER BY "LEADID" DESC'
        );

        return response()->json(array_map(fn ($r) => (array) $r, $rows));
    }

    public function leadsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'COMPANYNAME' => 'required|string',
            'CONTACTNAME' => 'nullable|string',
            'CONTACTNO' => 'nullable|string',
            'EMAIL' => 'nullable|string',
            'CITY' => 'nullable|string',
            'CURRENTSTATUS' => 'nullable|string',
            'ASSIGNED_TO' => 'nullable|integer',
        ]);

        $row = DB::selectOne(
            'INSERT INTO "LEAD" ("COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL","CITY","CURRENTSTATUS","ASSIGNED_TO","CREATEDAT","LASTMODIFIED")
             VALUES (?,?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)
             RETURNING "LEADID","CREATEDAT"',
            [
                $validated['COMPANYNAME'],
                $validated['CONTACTNAME'] ?? null,
                $validated['CONTACTNO'] ?? null,
                $validated['EMAIL'] ?? null,
                $validated['CITY'] ?? null,
                $validated['CURRENTSTATUS'] ?? null,
                $validated['ASSIGNED_TO'] ?? null,
            ]
        );

        return response()->json(['LEADID' => $row->LEADID, 'CREATEDAT' => $row->CREATEDAT], 201);
    }

    public function leadActivitiesIndex(int $leadId): JsonResponse
    {
        $rows = DB::select(
            'SELECT FIRST 200
                "LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS"
            FROM "LEAD_ACT"
            WHERE "LEADID" = ?
            ORDER BY "LEAD_ACTID" DESC',
            [$leadId]
        );

        return response()->json(array_map(fn ($r) => (array) $r, $rows));
    }

    public function leadActivitiesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'LEADID' => 'required|integer',
            'USERID' => 'required|integer',
            'SUBJECT' => 'nullable|string',
            'DESCRIPTION' => 'nullable|string',
            'ATTACHMENT' => 'nullable|string',
            'STATUS' => 'nullable|string',
        ]);

        $row = DB::selectOne(
            'INSERT INTO "LEAD_ACT" ("LEAD_ACTID","LEADID","USERID","CREATIONDATE","SUBJECT","DESCRIPTION","ATTACHMENT","STATUS")
             VALUES (GEN_ID("GEN_LEAD_ACTID", 1),?,?,CURRENT_TIMESTAMP,?,?,?,?)
             RETURNING "LEAD_ACTID","CREATIONDATE"',
            [
                $validated['LEADID'],
                $validated['USERID'],
                $validated['SUBJECT'] ?? null,
                $validated['DESCRIPTION'] ?? null,
                $validated['ATTACHMENT'] ?? null,
                $validated['STATUS'] ?? null,
            ]
        );

        DB::update('UPDATE "LEAD" SET "LASTMODIFIED" = CURRENT_TIMESTAMP WHERE "LEADID" = ?', [$validated['LEADID']]);

        return response()->json(['LEAD_ACTID' => $row->LEAD_ACTID, 'CREATIONDATE' => $row->CREATIONDATE], 201);
    }

    public function payoutsIndex(): JsonResponse
    {
        $rows = DB::select(
            'SELECT FIRST 200
                "REFERRERPAYOUTID","DEALSUBMISSIONID","USERID","STATUS","REFERRERID","DATEGENERATED","DATEPAID"
            FROM "REFERRER_PAYOUT"
            ORDER BY "REFERRERPAYOUTID" DESC'
        );

        return response()->json(array_map(fn ($r) => (array) $r, $rows));
    }

    public function usersIndex(): JsonResponse
    {
        $rows = DB::select(
            'SELECT "USERID","EMAIL","SYSTEMROLE","ISACTIVE","LASTLOGIN" FROM "USERS" ORDER BY "USERID"'
        );

        return response()->json(array_map(fn ($r) => (array) $r, $rows));
    }
}
