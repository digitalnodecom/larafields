<?php

namespace DigitalNode\Larafields;

use DigitalNode\Larafields\Services\FormRenderer;
use DigitalNode\Larafields\Services\WordPressHookService;
use Illuminate\Support\Collection;
use Roots\Acorn\Application;

class Larafields
{
    /**
     * The application instance.
     */
    protected Application $app;

    /**
     * Collection of form configurations.
     */
    protected Collection $forms;

    /**
     * Collection of page configurations.
     */
    protected Collection $pages;

    /**
     * WordPress Hook Service instance.
     */
    protected WordPressHookService $hookService;

    /**
     * Create a new Larafields instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->forms = collect([]);
        $this->pages = collect([]);

        $this->registerBasicHooks();

        add_action('wp_loaded', function (): void {
            $this->loadFormsAndPages();
            $this->registerFormSpecificHooks();
        });
    }

    /**
     * Load forms and pages when WordPress is fully loaded.
     */
    protected function loadFormsAndPages(): void
    {
        $this->forms = collect(
            apply_filters('larafields_load_forms', config('larafields.forms', []))
        );

        $this->pages = collect(
            apply_filters('larafields_load_pages', config('larafields.pages', []))
        );
    }

    protected function registerBasicHooks(): void
    {
        add_action('admin_enqueue_scripts', function (): void {
            if (asset('css/digitalnodecom/larafields.css')->exists()) {
                wp_enqueue_style(
                    'larafiels',
                    asset('css/digitalnodecom/larafields.css')->uri()
                );
            }
        });

        add_action('admin_menu', function (): void {
            $this->loadFormsAndPages();

            $adminMenuHookService = $this->app->makeWith(
                WordPressHookService::class,
                [
                    'forms' => $this->forms,
                    'pages' => $this->pages
                ]
            );

            $adminMenuHookService->registerAdminMenuHooks();
        }, 1);
    }

    protected function registerFormSpecificHooks(): void
    {
        $this->hookService = $this->app->makeWith(
            WordPressHookService::class,
            [
                'forms' => $this->forms,
                'pages' => $this->pages
            ]
        );

        $this->hookService->registerHooks();
    }


    /**
     * Add a new form group to the configuration.
     */
    public static function add_group(array $data): void
    {
        $forms = config('larafields.forms', []);
        $forms[] = $data;
        config(['larafields.forms' => $forms]);
    }
}
