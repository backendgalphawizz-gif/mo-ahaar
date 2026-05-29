<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver dispatch radius (km)
    |--------------------------------------------------------------------------
    |
    | When a driver sends latitude/longitude on the new-deliveries API, only
    | orders whose vendor pickup is within this radius are listed. If vendor
    | coordinates are missing, the order remains visible to all drivers.
    |
    */
    'dispatch_radius_km' => (float) env('DRIVER_DISPATCH_RADIUS_KM', 15),

];
