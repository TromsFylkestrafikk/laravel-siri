<?php

return [
    /*
    | ------------------------------------------------------------------------
    | Default settings for new subscriptions
    | ------------------------------------------------------------------------
    | - heartbeat_interval: ISO-8601 duration for delay between heartbeat requests
    |   should be sent from SIRI services.
    | - requestor_ref: Identifies client consuming siri data.
    */
    'subscription' => [
        'heartbeat_interval' => env('SIRI_SUB_HEARTBEAT_INTERVAL', 'PT1M'),
        'requestor_ref' => env('SIRI_SUB_REQUESTOR_REF', env('APP_NAME', 'Laravel SIRI consumer')),
    ],
];
