<?php

return [
    // Keep location targeting disabled until rollout is approved.
    'banner_location_enabled' => env('BANNER_LOCATION_ENABLED', false),

    // Planned placement keys for future location-specific banner delivery.
    'banner_location_options' => [
        'homepage_top',
        'homepage_middle',
        'homepage_bottom',
    ],
];
