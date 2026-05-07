<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configure analytics event tracking behavior
    |
    */

    'track_user_id' => env('ANALYTICS_TRACK_USER_ID', false),
    'track_user_email' => env('ANALYTICS_TRACK_USER_EMAIL', false),
    'suppress_pii' => env('ANALYTICS_SUPPRESS_PII', true),
];
