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
}
