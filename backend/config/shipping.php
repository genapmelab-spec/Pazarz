<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mock Shipping / Delivery Simulation
    |--------------------------------------------------------------------------
    | Development-only shipping simulation. No external courier API is used.
    |
    | transit_minutes: how long a shipment stays "in_transit" before the
    | simulation automatically marks it delivered. Set higher (e.g. 2880 = 2
    | days) for a production-like pace.
    */

    'mock' => [
        'courier' => env('MOCK_SHIPPING_COURIER', 'Pazarz Express'),
    ],

    'simulation' => [
        'transit_minutes' => (int) env('MOCK_SHIPPING_TRANSIT_MINUTES', 2),
    ],
];
