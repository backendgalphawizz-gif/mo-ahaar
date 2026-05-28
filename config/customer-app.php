<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default delivery fee (INR) shown on cart / payment screens
    |--------------------------------------------------------------------------
    */
    'delivery_fee' => (float) env('CUSTOMER_APP_DELIVERY_FEE', 40),

    /*
    |--------------------------------------------------------------------------
    | Nearby vendor search defaults
    |--------------------------------------------------------------------------
    */
    'nearby_radius_km' => (float) env('CUSTOMER_APP_NEARBY_RADIUS_KM', 15),
];
