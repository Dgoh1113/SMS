<?php

namespace App\Support;

/**
 * Centralized product definitions used across the application.
 */
class ProductConstants
{
    // Product ID mappings
    public const ACCOUNT = 1;

    public const PAYROLL = 2;

    public const PRODUCTION = 3;

    public const MOBILE_SALES = 4;

    public const ECOMMERCE = 5;

    public const EBI_POS = 6;

    public const SUDU_AI = 7;

    public const X_STORE = 8;

    public const VISION = 9;

    public const HRMS = 10;

    public const CTOS = 11;

    public const API = 12;

    public const OTHERS = 13;

    /**
     * Get all product labels indexed by ID
     */
    public static function all(): array
    {
        return [
            self::ACCOUNT => 'Account',
            self::PAYROLL => 'Payroll',
            self::PRODUCTION => 'Production',
            self::MOBILE_SALES => 'X-Mobile',
            self::ECOMMERCE => 'eCommerce',
            self::EBI_POS => 'EBI POS',
            self::SUDU_AI => 'x SuDu.Ai',
            self::X_STORE => 'X-Store',
            self::VISION => 'Vision',
            self::HRMS => 'HRMS',
            self::CTOS => 'CTOS',
            self::API => 'API',
            self::OTHERS => 'Others',
        ];
    }

    /**
     * Get full product names for inquiries/forms
     */
    public static function fullNames(): array
    {
        return [
            self::ACCOUNT => 'SQL Account',
            self::PAYROLL => 'SQL Payroll',
            self::PRODUCTION => 'SQL Production',
            self::MOBILE_SALES => 'SQL X-Mobile (SQL Mobile App)',
            self::ECOMMERCE => 'SQL eCommerce',
            self::EBI_POS => 'SQL EBI Wellness POS',
            self::SUDU_AI => 'SQL x SuDu.Ai',
            self::X_STORE => 'SQL X-Store',
            self::VISION => 'SQL Vision',
            self::HRMS => 'SQL HRMS',
            self::CTOS => 'SQL CTOS',
            self::API => 'SQL API',
            self::OTHERS => 'Others',
        ];
    }

    /**
     * Get product label by ID
     */
    public static function label(int $id): string
    {
        return self::all()[$id] ?? ('Product '.$id);
    }

    /**
     * Get full product name by ID
     */
    public static function fullName(int $id): string
    {
        return self::fullNames()[$id] ?? ('Product '.$id);
    }

    /**
     * Get all valid product IDs
     */
    public static function ids(): array
    {
        return array_keys(self::all());
    }

    /**
     * Check if ID is valid
     */
    public static function isValid(int $id): bool
    {
        return array_key_exists($id, self::all());
    }

    /**
     * Sort an array of product IDs based on the predefined UI color grouping order.
     * 
     * @param array $ids Array of product IDs to sort.
     * @return array The sorted array of product IDs.
     */
    public static function sortProductIds(array $ids): array
    {
        $pillOrder = [
            self::ACCOUNT => 10, self::PRODUCTION => 11, self::MOBILE_SALES => 12,
            self::PAYROLL => 20, self::HRMS => 21,
            self::X_STORE => 30, self::ECOMMERCE => 31,
            self::EBI_POS => 40,
            self::VISION => 50,
            self::SUDU_AI => 60,
            self::CTOS => 70, self::API => 80, self::OTHERS => 90
        ];

        usort($ids, function ($a, $b) use ($pillOrder) {
            $oa = $pillOrder[$a] ?? (1000 + $a);
            $ob = $pillOrder[$b] ?? (1000 + $b);
            return $oa <=> $ob;
        });

        return array_values(array_unique($ids));
    }
}
