<?php

namespace DigitalNode\FormMaker;

use DigitalNode\FormMaker\Traits\HandlesMetaBoxes;
use DigitalNode\FormMaker\Traits\HandlesMenuPages;
use DigitalNode\FormMaker\Traits\HandlesTermOptions;
use Illuminate\Support\Facades\Blade;
use Roots\Acorn\Application;

class FormMaker
{
    use HandlesMetaBoxes;
    use HandlesMenuPages;
    use HandlesTermOptions;

    /**
     * The application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected Application $app;

    /**
     * Create a new FormMaker instance.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->registerHooks();
    }

    /**
     * Register all WordPress hooks.
     *
     * @return void
     */
    protected function registerHooks(): void
    {
        $this->registerAssetHooks();
        $this->registerMetaBoxHooks();
        $this->registerMenuPageHooks();
        $this->registerTermOptionHooks();
    }

    /**
     * Register asset-related hooks.
     *
     * @return void
     */
    protected function registerAssetHooks(): void
    {
        add_filter('wp_head', fn() => Blade::render('@livewireStyles'));
        add_filter('wp_footer', fn() => Blade::render('@livewireScripts'));
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    /**
     * Register meta box related hooks.
     *
     * @return void
     */
    protected function registerMetaBoxHooks(): void
    {
        add_action('add_meta_boxes', [$this, 'initializeMetaBoxes']);
        add_action('wp_loaded', [$this, 'initializeMetaBoxes']);
    }

    /**
     * Register menu page related hooks.
     *
     * @return void
     */
    protected function registerMenuPageHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPages']);
    }

    /**
     * Register term option related hooks.
     *
     * @return void
     */
    protected function registerTermOptionHooks(): void
    {
        add_filter('init', [$this, 'initializeTermOptions'], 10, 2);
        add_action('admin_menu', [$this, 'createPlaceholderTermOptionPage']);
    }

    /**
     * Enqueue admin assets.
     *
     * @return void
     */
    public function enqueueAdminAssets(): void
    {
        // TODO: use local .js and .css files.
        wp_enqueue_script(
            'choices-js',
            'https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.4.1/js/tom-select.complete.js'
        );
        wp_enqueue_style(
            'choices-css',
            'https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.4.1/css/tom-select.css'
        );
    }
}
