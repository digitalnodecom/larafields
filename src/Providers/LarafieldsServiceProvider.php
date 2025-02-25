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
        $this->loadViewsFrom(
            __DIR__.'/../../resources/views',
            'Larafields',
        );

        Blade::anonymousComponentPath(__DIR__.'/../../resources/views/components/');

        $this->publishes([
            __DIR__.'/../../resources/styles/public' => public_path('css/digitalnodecom'),
        ], 'larafields');
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
