<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 3) {
            return response()->json(['error' => 'Please enter at least 3 characters.'], 422);
        }

        $config = config('services.sql_account');
        $baseUrl = $config['base_url'] ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        if (!$baseUrl || !$username || !$password) {
            return response()->json(['error' => 'API configuration missing.'], 500);
        }

        try {
            // The API supports offset and limit. We'll search by company name or code.
            // Note: The provided documentation doesn't explicitly show a "search" parameter,
            // but usually these APIs have one. If not, we might need to fetch and filter,
            // but that's inefficient. We'll try to use a 'q' or 'search' parameter.
            // If the API only supports pagination, we'll just fetch the first page for now.
            
            $response = Http::withBasicAuth($username, $password)
                ->withoutVerifying()
                ->get($baseUrl . '/customer', [
                    'limit' => 50,
                    'offset' => 0,
                    'search' => $query // Assuming 'search' or similar is supported
                ]);

            if ($response->failed()) {
                Log::error('SQL Account API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json(['error' => 'Failed to fetch customer data.'], 502);
            }

            $data = $response->json();
            
            // If the API doesn't support server-side search, we filter locally on the first 50 results
            // as a fallback, or just return the data if it matches.
            $customers = $data['data'] ?? [];
            
            $filtered = array_filter($customers, function ($c) use ($query) {
                $q = strtolower($query);
                return str_contains(strtolower($c['companyname'] ?? ''), $q) || 
                       str_contains(strtolower($c['code'] ?? ''), $q);
            });

            return response()->json([
                'customers' => array_values($filtered)
            ]);

        } catch (\Throwable $e) {
            Log::error('Customer search error', ['msg' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred during search.'], 500);
        }
    }
}
