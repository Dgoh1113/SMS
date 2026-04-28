<?php

namespace App\Http\Controllers;

use App\Support\AppConstants;
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
            'error' => AppConstants::ERR_API_LOGIN_NOT_AVAILABLE,
        ], 410);
    }

    public function leadsIndex(): JsonResponse
    {
        $rows = DB::select(
            'SELECT FIRST 200
                l."LEADID",l."PRODUCTID",l."COMPANYNAME",l."CONTACTNAME",l."CONTACTNO",l."EMAIL",l."ADDRESS1",l."ADDRESS2",
                l."CITY",l."STATE",l."COUNTRY",l."POSTCODE",l."BUSINESSNATURE",l."USERCOUNT",l."EXISTINGSOFTWARE",l."DEMOMODE",l."DESCRIPTION",
                l."REFERRALCODE",
                COALESCE(
                    (SELECT FIRST 1 la."STATUS"
                     FROM "LEAD_ACT" la
                     WHERE la."LEADID" = l."LEADID"
                     ORDER BY la."CREATIONDATE" DESC, la."LEAD_ACTID" DESC),
                    \'Created\'
                ) AS "CURRENTSTATUS",
                l."CREATEDAT",l."CREATEDBY",l."ASSIGNEDTO" AS "assignedTo",l."LASTMODIFIED"
            FROM "LEAD" l
            ORDER BY l."LEADID" DESC'
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
            'STATE' => 'nullable|string',
            'COUNTRY' => 'nullable|string',
            'assignedTo' => 'nullable|integer',
        ]);

        $row = DB::selectOne(
            'INSERT INTO "LEAD" ("COMPANYNAME","CONTACTNAME","CONTACTNO","EMAIL","CITY","STATE","COUNTRY","ASSIGNEDTO","CREATEDAT","LASTMODIFIED")
             VALUES (?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)
             RETURNING "LEADID","CREATEDAT"',
            [
                $validated['COMPANYNAME'],
                $validated['CONTACTNAME'] ?? null,
                $validated['CONTACTNO'] ?? null,
                $validated['EMAIL'] ?? null,
                $validated['CITY'] ?? null,
                $validated['STATE'] ?? null,
                $validated['COUNTRY'] ?? null,
                $validated['assignedTo'] ?? null,
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

