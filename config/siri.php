<?php

return [

    /*
    | ------------------------------------------------------------------------
    | Route prefix and middleware
    | ------------------------------------------------------------------------
    |
    | The prefix sets the global prefix for all requests within this package.
    | Defaults to 'siri'.
    |
    | The middleware lists the middleware this package should consume.
    |
    | In addition, the 'route_dev' group has the same meaning, though only
    | useful during local development. Use 'enabled' to turn on or off this set
    | of routes.
    */
    'routes_api' => [
        'prefix' => 'api/siri',
        'middleware' => ['api'],
    ],
    'routes_web' => [
        'prefix' => 'siri',
        'middleware' => ['web'],
    ],

    /*
    | ------------------------------------------------------------------------
    | Enable routes used during development.
    | ------------------------------------------------------------------------
     */
    'enable_dev_routes' => env('APP_ENV') === 'local',

    /*
    | ------------------------------------------------------------------------
    | Default settings for new subscriptions
    | ------------------------------------------------------------------------
    |
    | - heartbeat_interval: ISO-8601 duration for delay between heartbeat requests
    |   should be sent from SIRI services.
    | - requestor_ref: Identifies client consuming siri data.
    */
    'subscription' => [
        'heartbeat_interval' => env('SIRI_SUB_HEARTBEAT_INTERVAL', 'PT1M'),
        'requestor_ref' => env('SIRI_SUB_REQUESTOR_REF', env('APP_NAME', 'Laravel SIRI consumer')),
    ],

    /*
    | ------------------------------------------------------------------------
    | File system disk for SIRI related files.
    | ------------------------------------------------------------------------
    |
    | Use this storage disk when writing files related to this package.
    | Files written here include:
    | - Subscription request error responses
    | - Consumed ET/VM/SX data from SIRI service provider (if saved).
    | - Last failed ET/SX/VM post.
    */
    'disk' => env('SIRI_DISK', 'local'),

    /*
    | ------------------------------------------------------------------------
    | Folder on siri disk for SIRI data.
    | ------------------------------------------------------------------------
    */
    'folder' => 'siri',

    /*
    | ------------------------------------------------------------------------
    | Parse XML in queued jobs past this pivot size
    | ------------------------------------------------------------------------
    |
    | Send parsing of consumed XMLs to queued jobs when files become larger than
    | this, per channel.
    */
    'queue_pivot' => [
        'ET' => 64000,
        'SX' => 64000,
        'VM' => 64000,
    ],

    /*
    | ------------------------------------------------------------------------
    | Save XMLs
    | ------------------------------------------------------------------------
    |
    | All incoming XMLs are saved during processing. Set these to save the xmls
    | permanently for each channel.
    */
    'save_xml' => [
        'ET' => false,
        'SX' => false,
        'VM' => false,
    ],
];
