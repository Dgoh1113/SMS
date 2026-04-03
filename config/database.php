<?php

return [
    'default' => env('DB_CONNECTION', 'firebird'),

    'connections' => [
        'firebird' => [
            'driver' => 'firebird',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3050'),
            'database' => env('DB_DATABASE', '/path_to/database.fdb'),
            'username' => env('DB_USERNAME', 'sysdba'),
            'password' => env('DB_PASSWORD', 'masterkey'),
            'charset' => env('DB_CHARSET', 'UTF8'),
            'role' => env('DB_ROLE'),
            'legacy_limit_and_offset' => env('DB_LEGACY_LIMIT_AND_OFFSET', false),
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],
];
