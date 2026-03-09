<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Passkey / WebAuthn (same concept as SQL SMS PHP reference project)
    |--------------------------------------------------------------------------
    | For local dev we normalize 127.0.0.1 → localhost. Use http://localhost:PORT
    | (e.g. http://localhost:8080) so the browser origin matches rpId.
    */

    'rp_name' => env('PASSKEY_RP_NAME', env('APP_NAME', 'SMS')),

    /*
     * If set, used as rpId (e.g. "sms.example.com"). Leave null to use request host.
     */
    'rp_id' => env('PASSKEY_RP_ID'),
];
