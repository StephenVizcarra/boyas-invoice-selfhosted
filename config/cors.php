<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Currently set to allow all origins for local development.
    |
    | Before public deployment, restrict allowed_origins to your actual domain
    | and any mobile app origins, e.g.:
    |
    |   'allowed_origins' => ['https://yourdomain.com'],
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Invoice-Number'],

    'max_age' => 0,

    'supports_credentials' => false,

];
