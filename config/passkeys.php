<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Passkey / WebAuthn
    |--------------------------------------------------------------------------
    | rp_name is shown in the browser during passkey creation. rp_id must match
    | your app origin; leave null to use the request host.
    */

    'rp_name' => env('PASSKEY_RP_NAME', env('APP_NAME', 'SMS')),

    /*
     * If set, used as rpId (e.g. "sms.example.com"). Leave null to use request host.
     */
    'rp_id' => env('PASSKEY_RP_ID'),
];
