# Laravel Acorn Form Builder Package for WordPress

A flexible form builder package for WordPress using Laravel Acorn.

## Asset Management

This package includes a hybrid asset management system that works in both development and production environments.

### Development Mode

In development mode, assets are served dynamically through a Laravel route. This allows for hot-reloading and faster development.

To enable development mode:

1. Set the environment variable in your `.env` file:
   ```
   LARAFIELDS_ASSET_MODE=development
   ```

2. Run the development build with source maps:
   ```bash
   npm run build:dev
   ```

3. Or use watch mode for automatic rebuilding:
   ```bash
   npm run dev
   ```

### Production Mode

In production mode, assets are published to the WordPress public directory for better performance.

To use production mode:

1. Set the environment variable in your `.env` file (or leave it unset as it's the default):
   ```
   LARAFIELDS_ASSET_MODE=production
   ```

2. Build the assets for production:
   ```bash
   npm run build
   ```

3. Publish the assets:
   ```php
   php artisan vendor:publish --tag=larafields
   ```

### Customizing Styles

The package uses Tailwind CSS for styling. You can customize the styles by:

1. Editing the `resources/styles/input.css` file
2. Modifying the `tailwind.config.js` file
3. Rebuilding the assets

## WordPress Integration

The package automatically registers and enqueues the necessary styles in WordPress admin based on the configured mode.

## Configuration

You can customize the asset configuration in `config/larafields.php`:

```php
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
```
