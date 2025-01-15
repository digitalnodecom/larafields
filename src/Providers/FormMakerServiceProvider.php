<?php

namespace DigitalNode\FormMaker\Providers;

use DigitalNode\FormMaker\Component\FormMakerComponent;
use DigitalNode\FormMaker\FormMaker;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FormMakerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('FormMaker', function () {
            return new FormMaker($this->app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureViews();
        $this->configureLivewire();
        $this->configureApp();
        $this->configureConfig();
    }

    private function configureViews() {
        $this->loadViewsFrom(
            __DIR__.'/../../resources/views/',
            'FormMaker',
        );
    }

    private function configureLivewire() {
        Livewire::component('FormMaker', FormMakerComponent::class);
    }

    private function configureApp() {
        $this->app->make(FormMaker::class);
    }

    private function configureConfig() {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/form-maker.php',
            'form-maker'
        );
    }
}

