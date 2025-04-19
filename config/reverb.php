<?php
// filepath: config/reverb.php

return [

    /*
    |--------------------------------------------------------------------------
    | Reverb Apps
    |--------------------------------------------------------------------------
    |
    | Define one or more apps that will be served by this installation.
    |
    */
    'apps' => [
        [
            'app_id'         => env('REVERB_APP_ID'),
            'key'            => env('REVERB_APP_KEY'),
            'secret'         => env('REVERB_APP_SECRET'),
            'allowed_origins'=> ['*'],        // or ['your-domain.com']
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Server Settings
    |--------------------------------------------------------------------------
    */
    'host'   => env('REVERB_HOST', '127.0.0.1'),
    'port'   => env('REVERB_PORT', 8081),
    'scheme' => env('REVERB_SCHEME', 'https'),

    /*
    |--------------------------------------------------------------------------
    | TLS Options (local development only)
    |--------------------------------------------------------------------------
    */
    'options' => [
        'tls' => [
            // 'local_cert' => '/path/to/cert.pem',
        ],
    ],

];