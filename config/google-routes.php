<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Routes API Key
    |--------------------------------------------------------------------------
    |
    | Your Google Cloud API key with Routes API enabled. You can obtain one
    | from the Google Cloud Console: https://console.cloud.google.com/
    |
    */
    'api_key' => env('GOOGLE_ROUTES_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Google Routes API. You shouldn't need to change
    | this unless you're using a proxy or testing with a mock server.
    |
    */
    'base_url' => env('GOOGLE_ROUTES_BASE_URL', 'https://routes.googleapis.com'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests. Increase this value if you're
    | experiencing timeout issues with complex route calculations.
    |
    */
    'timeout' => env('GOOGLE_ROUTES_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default values for route requests. These can be overridden on a
    | per-request basis using the fluent API.
    |
    */
    'defaults' => [
        /*
        | Default travel mode for routes.
        | Options: DRIVE, BICYCLE, WALK, TWO_WHEELER, TRANSIT
        */
        'travel_mode' => env('GOOGLE_ROUTES_TRAVEL_MODE', 'DRIVE'),

        /*
        | Default language code for responses (BCP-47 format).
        | Examples: en-US, es-ES, fr-FR, de-DE, ja-JP
        */
        'language_code' => env('GOOGLE_ROUTES_LANGUAGE', 'en-US'),

        /*
        | Default unit system for distances.
        | Options: METRIC, IMPERIAL
        */
        'units' => env('GOOGLE_ROUTES_UNITS', 'METRIC'),

        /*
        | Default routing preference.
        | Options: TRAFFIC_UNAWARE, TRAFFIC_AWARE, TRAFFIC_AWARE_OPTIMAL
        */
        'routing_preference' => env('GOOGLE_ROUTES_ROUTING_PREFERENCE', 'TRAFFIC_AWARE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Field Mask
    |--------------------------------------------------------------------------
    |
    | Fields to include in the response by default. This helps reduce response
    | size and API costs. Override per-request with the fields() method.
    |
    | Common fields:
    | - routes.duration
    | - routes.distanceMeters
    | - routes.polyline.encodedPolyline
    | - routes.legs
    | - routes.viewport
    |
    */
    'default_field_mask' => [
        'routes.duration',
        'routes.distanceMeters',
        'routes.polyline.encodedPolyline',
    ],
];
