<?php

namespace DigitalNode\Larafields\Providers;

use DigitalNode\Larafields\Component\FormMakerComponent;
use DigitalNode\Larafields\Larafields;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LarafieldsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('FormMaker', function () {
            return new Larafields($this->app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureDatabase();
        $this->configureAssets();
        $this->configureLivewire();
        $this->configureRoutes();
        $this->configureConfig();
        $this->configureApp();
    }

    private function configureAssets()
    {
        // Load views
        $this->loadViewsFrom(
            __DIR__.'/../../resources/views',
            'Larafields',
        );

        // Register Blade components
        Blade::anonymousComponentPath(__DIR__.'/../../resources/views/components/');

        // Publish assets for production mode
        $this->publishes([
            __DIR__.'/../../resources/styles/public' => public_path('css/digitalnodecom'),
        ], 'larafields');
        
        // Register asset routes for development mode
        if (config('larafields.assets.mode', 'production') === 'development') {
            // Routes are already registered in routes/api.php
            // We just need to ensure the CSS file exists
            $this->ensureCompiledCssExists();
        }
    }
    
    /**
     * Ensure the compiled CSS file exists.
     * This is useful for development mode when the file might not have been compiled yet.
     */
    private function ensureCompiledCssExists()
    {
        $cssPath = __DIR__ . '/../../resources/styles/public/larafields.css';
        
        if (!file_exists($cssPath)) {
            // If the CSS file doesn't exist, create a directory if needed
            $dir = dirname($cssPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Create an empty CSS file to prevent 404 errors
            file_put_contents($cssPath, '/* This file will be replaced by the compiled CSS */');
        }
    }

    private function configureLivewire()
    {
        Livewire::component('FormMaker', FormMakerComponent::class);
    }

    private function configureApp()
    {
        $this->app->make(Larafields::class);
    }

    private function configureConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/larafields.php',
            'larafields'
        );
    }

    private function configureDatabase()
    {
        $this->publishesMigrations([
            __DIR__.'/../../database/migrations' => $this->app->databasePath('migrations'),
        ], 'larafields');
    }

    private function configureRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
    }
}
