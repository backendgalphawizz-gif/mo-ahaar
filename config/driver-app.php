<?php

return [
  /*
  |--------------------------------------------------------------------------
  | Nearby driver search radius (km) for automatic assignment broadcasts
  |--------------------------------------------------------------------------
  */
  'nearby_radius_km' => (float) env('DRIVER_APP_NEARBY_RADIUS_KM', 15),

  /*
  |--------------------------------------------------------------------------
  | Max drivers to notify per broadcast
  |--------------------------------------------------------------------------
  */
  'broadcast_max_drivers' => (int) env('DRIVER_APP_BROADCAST_MAX_DRIVERS', 25),
];
