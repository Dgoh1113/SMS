<?php

namespace App\Support;

/**
 * Centralized string manipulation helper to reduce code duplication.
 * Provides safe, null-aware string operations used throughout the app.
 */
class StringHelper
{
    /**
     * Safely trim and cast value to string, handling nulls gracefully.
     * Eliminates repeated pattern: trim((string) ($var ?? ''))
     *
     * @param  mixed  $value  Value to normalize
     * @param  string  $fallback  Default if value is empty
     * @return string Normalized string
     */
    public static function normalize(mixed $value, string $fallback = ''): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? $fallback : $normalized;
    }

    /**
     * Safely get a property value from an object, with normalization.
     * Reduces repeated pattern: trim((string) ($obj->PROP ?? $obj->prop ?? ''))
     *
     * @param  object|array  $object  Object or array to get property from
     * @param  string  $property  Case-sensitive property name (try uppercase first)
     * @return string Normalized value
     */
    public static function getProperty(object|array $object, string $property, string $fallback = ''): string
    {
        // For arrays
        if (is_array($object)) {
            $value = $object[$property] ?? $object[strtolower($property)] ?? null;

            return self::normalize($value, $fallback);
        }

        // For objects - try uppercase first (Firebird style), then lowercase
        $value = $object->$property ?? $object->{strtolower($property)} ?? null;

        return self::normalize($value, $fallback);
    }

    /**
     * Safely cast to integer, returning 0 for invalid values.
     *
     * @param  mixed  $value  Value to cast
     * @param  int  $fallback  Default if value is invalid
     * @return int Integer value
     */
    public static function toInteger(mixed $value, int $fallback = 0): int
    {
        if ($value === null) {
            return $fallback;
        }

        $intValue = (int) $value;

        return $intValue > 0 ? $intValue : $fallback;
    }

    /**
     * Remove all non-digit characters from a string.
     * Useful for postcode normalization.
     *
     * @param  string|null  $value  String to normalize
     * @return string String with only digits
     */
    public static function digitsOnly(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $result = preg_replace('/\D+/', '', $value);

        return $result === null ? '' : $result;
    }

    /**
     * Check if a trimmed string equals a value (case-insensitive).
     *
     * @param  mixed  $value  Value to check
     * @param  string  $comparison  Value to compare against
     * @return bool True if equal (case-insensitive)
     */
    public static function equalsIgnoreCase(mixed $value, string $comparison): bool
    {
        return strtoupper(self::normalize($value)) === strtoupper($comparison);
    }

    /**
     * Check if trimmed string starts with another (case-insensitive).
     *
     * @param  mixed  $value  Value to check
     * @param  string  $prefix  Prefix to check for
     * @return bool True if starts with
     */
    public static function startsWithIgnoreCase(mixed $value, string $prefix): bool
    {
        $normalized = strtoupper(self::normalize($value));

        return str_starts_with($normalized, strtoupper($prefix));
    }

    /**
     * Build display text from user properties with fallback chain.
     * Reduces repeated logic: company+alias > company > alias > email > id
     *
     * @param  object  $user  User object with properties
     * @param  string  $separator  Separator between multiple properties
     * @return string Display name
     */
    public static function buildUserDisplayName(object $user, string $separator = ' - '): string
    {
        $company = self::getProperty($user, 'COMPANY');
        $alias = self::getProperty($user, 'ALIAS');
        $email = self::getProperty($user, 'EMAIL');
        $id = self::getProperty($user, 'USERID');

        if ($company !== '' && $alias !== '') {
            return $company.$separator.$alias;
        }
        if ($company !== '') {
            return $company;
        }
        if ($alias !== '') {
            return $alias;
        }
        if ($email !== '') {
            return $email;
        }

        return $id;
    }

    /**
     * Build actor display text from user properties with role prefix.
     * Used for activity tracking display.
     *
     * @param  object  $user  User object with properties
     * @param  string  $separator  Separator between elements
     * @return string Display text
     */
    public static function buildUserActorName(object $user, string $separator = ' - '): string
    {
        $role = self::getProperty($user, 'SYSTEMROLE');
        $alias = self::getProperty($user, 'ALIAS');
        $company = self::getProperty($user, 'COMPANY');
        $email = self::getProperty($user, 'EMAIL');
        $id = self::getProperty($user, 'USERID');

        $nameDisplay = $alias !== '' ? $alias : ($company !== '' ? $company : ($email !== '' ? $email : $id));

        if ($role !== '') {
            return $role.$separator.$nameDisplay;
        }

        return $nameDisplay;
    }
}
