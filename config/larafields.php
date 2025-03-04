<?php

return [
    'forms' => [],
    
    /*
    |--------------------------------------------------------------------------
    | Asset Configuration
    |--------------------------------------------------------------------------
    |
    | This section configures how assets are handled in the package.
    | You can choose between development and production modes,
    | configure caching, and set paths for assets.
    |
    */
    'assets' => [
        // Asset mode: 'development' or 'production'
        'mode' => env('LARAFIELDS_ASSET_MODE', 'production'),
        
        // Cache control header for production mode
        'cache_control' => 'public, max-age=31536000',
        
        // Development mode settings
        'development' => [
            // Base URL for dynamic asset loading in development
            'url' => env('LARAFIELDS_DEV_ASSET_URL', '/api/larafields/assets'),
        ],
        
        // Production mode settings
        'production' => [
            // Base URL for published assets in production
            'url' => env('LARAFIELDS_PROD_ASSET_URL', '/css/digitalnodecom'),
        ],
    ],
];
