<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Common database aggregation methods to reduce code duplication.
 */
trait DatabaseAggregates
{
    /**
     * Get a count value safely from database result, handling case variations.
     */
    protected function getCountFromResult(object|array $row, string $field = 'c'): int
    {
        if (is_array($row)) {
            foreach ([$field, strtoupper($field), strtolower($field)] as $key) {
                if (array_key_exists($key, $row)) {
                    return (int) $row[$key];
                }
            }

            return 0;
        }

        foreach ([$field, strtoupper($field), strtolower($field)] as $prop) {
            if (property_exists($row, $prop)) {
                return (int) $row->{$prop};
            }
        }

        return 0;
    }

    /**
     * Get a property safely from database result, handling case variations.
     */
    protected function getPropertyFromResult(object|array $row, string $name, mixed $default = null): mixed
    {
        if (is_array($row)) {
            foreach ([$name, strtoupper($name), strtolower($name)] as $key) {
                if (array_key_exists($key, $row)) {
                    return $row[$key];
                }
            }

            return $default;
        }

        foreach ([$name, strtoupper($name), strtolower($name)] as $prop) {
            if (property_exists($row, $prop)) {
                return $row->{$prop};
            }
        }

        return $default;
    }

    /**
     * Count records with flexible case-insensitive field access.
     */
    protected function countByStatus(string $table, string $statusField, string $status, array $whereConditions = []): int
    {
        $query = "SELECT COUNT(*) as c FROM \"$table\" WHERE UPPER(TRIM(\"$statusField\")) = UPPER(TRIM(?))";
        $bindings = [$status];

        foreach ($whereConditions as $condition => $values) {
            $query .= ' AND '.$condition;
            $bindings = array_merge($bindings, (array) $values);
        }

        $result = DB::selectOne($query, $bindings);

        return $this->getCountFromResult($result);
    }

    /**
     * Get counts by status group - useful for dashboard metrics
     */
    protected function getCountsByStatus(string $table, string $statusField, string $countField = 'c'): array
    {
        $query = "SELECT \"$statusField\" as status, COUNT(*) as $countField FROM \"$table\" GROUP BY \"$statusField\"";
        $rows = DB::select($query);

        $counts = [];
        foreach ($rows as $row) {
            $status = (string) $this->getPropertyFromResult($row, 'status');
            $count = $this->getCountFromResult($row, $countField);
            $counts[$status] = $count;
        }

        return $counts;
    }

    /**
     * Calculate percentage change between two values
     */
    protected function calculatePercentChange(int|float $current, int|float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        $change = (($current - $previous) / $previous) * 100;

        return abs($change) < 0.05 ? 0.0 : round($change, 1);
    }
}
