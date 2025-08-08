<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Facebook App Configuration
    |--------------------------------------------------------------------------
    |
    | Your Facebook App ID and App Secret from the Facebook Developers Console.
    | You can find these at https://developers.facebook.com/apps/
    |
    */
    'app_id' => env('FACEBOOK_APP_ID', ''),
    'app_secret' => env('FACEBOOK_APP_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Graph API Version
    |--------------------------------------------------------------------------
    |
    | The default Facebook Graph API version to use for all requests.
    | You can override this per request if needed.
    |
    */
    'default_graph_version' => env('FACEBOOK_GRAPH_VERSION', 'v18.0'),

    /*
    |--------------------------------------------------------------------------
    | Default Access Token
    |--------------------------------------------------------------------------
    |
    | A default access token to use for requests. This is optional and can
    | be overridden per request.
    |
    */
    'default_access_token' => env('FACEBOOK_ACCESS_TOKEN', null),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for HTTP requests to Facebook Graph API.
    |
    */
    'timeout' => env('FACEBOOK_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for retrying failed requests.
    |
    */
    'retry' => [
        'enabled' => env('FACEBOOK_RETRY_ENABLED', true),
        'max_attempts' => env('FACEBOOK_RETRY_MAX_ATTEMPTS', 3),
        'delay' => env('FACEBOOK_RETRY_DELAY', 1000), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for logging API requests and responses.
    |
    */
    'logging' => [
        'enabled' => env('FACEBOOK_LOGGING_ENABLED', false),
        'channel' => env('FACEBOOK_LOGGING_CHANNEL', 'stack'),
        'level' => env('FACEBOOK_LOGGING_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching API responses.
    |
    */
    'cache' => [
        'enabled' => env('FACEBOOK_CACHE_ENABLED', false),
        'ttl' => env('FACEBOOK_CACHE_TTL', 3600), // seconds
        'prefix' => env('FACEBOOK_CACHE_PREFIX', 'facebook_graph_api'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Facebook Webhooks.
    |
    */
    'webhook' => [
        'verify_token' => env('FACEBOOK_WEBHOOK_VERIFY_TOKEN', ''),
        'app_secret_proof' => env('FACEBOOK_APP_SECRET_PROOF', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Fields for Common Endpoints
    |--------------------------------------------------------------------------
    |
    | Default fields to request for common endpoints.
    |
    */
    'default_fields' => [
        'user' => ['id', 'name', 'email', 'picture'],
        'page' => ['id', 'name', 'fan_count', 'category', 'picture'],
        'post' => ['id', 'message', 'created_time', 'type', 'permalink_url'],
    ],
]; 