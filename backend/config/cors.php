<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Pazarz runs two servers: the React customer storefront on
    | http://localhost:5173 (Vite) and the Laravel backend/dashboard on
    | http://127.0.0.1:8000. In dev the storefront calls the API through
    | Vite's proxy (same-origin), but the Midtrans Snap popup and direct
    | browser calls may hit Laravel cross-origin, so 5173 is allowed.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:8000',
        'http://localhost:8000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
