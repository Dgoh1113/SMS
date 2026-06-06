<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Query result caching helper for expensive database operations
 *
 * Reduces repeated database calls by caching results with configurable TTL
 */
class QueryCache
{
    /**
     * Cache a database query result
     *
     * @param  string  $key  Unique cache key
     * @param  \Closure  $query  Database query closure
     * @param  int  $ttlMinutes  Time-to-live in minutes (default 60 minutes)
     * @return mixed Query result
     *
     * Usage:
     * $dealers = QueryCache::remember('dealers.all', function () {
     *     return DB::select('SELECT ... FROM "USERS" WHERE ...');
     * });
     */
    public static function remember(string $key, \Closure $query, int $ttlMinutes = 60): mixed
    {
        return Cache::remember($key, $ttlMinutes * 60, $query);
    }

    /**
     * Cache dashboard data (heavy operation)
     */
    public static function dashboardData(\Closure $callback, int $ttlMinutes = 5): mixed
    {
        return self::remember('admin.dashboard.data', $callback, $ttlMinutes);
    }

    /**
     * Cache report scope options
     */
    public static function reportScopes(\Closure $callback, int $ttlMinutes = 30): mixed
    {
        return self::remember('admin.report.scopes', $callback, $ttlMinutes);
    }

    /**
     * Cache postcode/city lookup
     */
    public static function postcodeLookup(\Closure $callback, int $ttlMinutes = 60): mixed
    {
        return self::remember('admin.postcode.lookup', $callback, $ttlMinutes);
    }

    /**
     * Invalidate specific cache
     */
    public static function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Invalidate all admin-related caches
     */
    public static function forgetAdminCaches(): void
    {
        Cache::forget('admin.dashboard.data');
        Cache::forget('admin.report.scopes');
        Cache::forget('admin.postcode.lookup');
        Cache::forget('admin.dealer.stats');
    }

    /**
     * Clear all application caches
     */
    public static function forgetAll(): void
    {
        Cache::flush();
    }
}
