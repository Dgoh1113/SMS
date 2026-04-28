<?php

namespace App\Support;

/**
 * Application-wide constants for status values, roles, and other enums.
 * Replaces magic strings throughout the codebase.
 */
class AppConstants
{
    // System Roles
    public const ROLE_ADMIN = 'Admin';
    public const ROLE_DEALER = 'Dealer';

    // Lead Status Values
    public const STATUS_OPEN = 'OPEN';
    public const STATUS_CREATED = 'CREATED';

    public const STATUS_COMPLETED = 'COMPLETED';  // Used in LEAD_ACT
    public const STATUS_FAILED = 'FAILED';

    // Lead Activity Status
    public const ACTIVITY_STATUS_LEAD_ASSIGNED = 'LEAD ASSIGNED';
    public const ACTIVITY_STATUS_COMPLETED = 'COMPLETED';
    public const ACTIVITY_STATUS_REWARDED = 'REWARDED';
    public const ACTIVITY_STATUS_DEALT_PRODUCT = 'DEALT PRODUCT';

    // Lead Activity Subjects
    public const SUBJECT_LEAD_ASSIGNED = 'Lead Assigned';

    // Database Columns (Firebird style - uppercase)
    public const DB_COLUMN_LEADID = 'LEADID';
    public const DB_COLUMN_USERID = 'USERID';
    public const DB_COLUMN_STATUS = 'STATUS';
    public const DB_COLUMN_CREATIONDATE = 'CREATIONDATE';
    public const DB_COLUMN_assignedTo = 'assignedTo';


    // Cache Keys
    public const CACHE_KEY_POSTCODE_LOOKUP = 'postcode_city_lookup';
    public const CACHE_KEY_LATEST_ASSIGNMENT = 'latest_assignment:';
    public const CACHE_KEY_USER_DISPLAY_MAPS = 'user_display_maps:';
    public const CACHE_KEY_DASHBOARD_DATA = 'admin.dashboard.data';

    // Query Limits
    public const QUERY_LIMIT_LEADS_INDEX = 200;

    // Error Messages
    public const ERR_INQUIRY_NOT_FOUND = 'Lead not found.';
    public const ERR_INQUIRY_ALREADY_ASSIGNED = 'This inquiry is already assigned to %s. Please sync and try again.';
    public const ERR_INQUIRY_ALREADY_PROCESSED = 'This inquiry is already %s. Please sync and try again.';
    public const ERR_API_LOGIN_NOT_AVAILABLE = 'API login is not available. Use the web app passkey flow instead.';

    // Session Keys
    public const SESSION_LOGIN_FAIL_COUNTS = 'login_fail_counts';
    public const SESSION_USER_ID = 'user_id';
    public const SESSION_USER_ROLE = 'user_role';
    public const SESSION_PASSKEY_SETUP_REQUIRED = 'passkey_setup_required';

    // Display Separators
    public const SEPARATOR_DISPLAY = ' - ';
    public const SEPARATOR_PROPERTY = '-';
}

