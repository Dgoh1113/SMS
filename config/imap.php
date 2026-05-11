<?php

return [
    'default' => env('IMAP_DEFAULT_ACCOUNT', 'default'),
    'accounts' => [
        'default' => [
            'host'  => env('IMAP_HOST', 'localhost'),
            'port'  => env('IMAP_PORT', 993),
            'protocol'  => 'imap', // protocol expected
            'encryption'    => env('IMAP_ENCRYPTION', 'ssl'),
            'validate_cert' => env('IMAP_VALIDATE_CERT', true),
            'username' => env('IMAP_USERNAME', 'root@example.com'),
            'password' => env('IMAP_PASSWORD', ''),
            'authentication' => null,
            'proxy' => [
                'socket' => null,
                'request_fulluri' => false,
                'username' => null,
                'password' => null,
            ]
        ],
    ],
    'options' => [
        'delimiter' => '/',
        'fetch' => \Webklex\PHPIMAP\IMAP::FT_UID,
        'fetch_order' => 'asc',
        'open' => [
            // 'DISABLE_AUTHENTICATOR' => 'GSSAPI'
        ]
    ],
    'flags' => ['RECENT', 'SEEN', 'ANSWERED', 'FLAGGED', 'DELETED', 'DRAFT'],
];
