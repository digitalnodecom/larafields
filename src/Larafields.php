<?php

namespace DigitalNode\Larafields;

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
     * Create a new Larafields instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->forms = collect(
            apply_filters('larafields_load_forms', config('larafields.forms', []))
        );

        $this->initializeWordPressHooks();

        add_action('admin_enqueue_scripts', function (): void {
            if (  asset( 'css/digitalnodecom/larafields.css' )->exists() ){
                wp_enqueue_style(
                    'larafiels',
                    asset( 'css/digitalnodecom/larafields.css' )->uri()
                );
            }
        });
    }

    /**
     * Initialize WordPress hooks through the dedicated service.
     */
    protected function initializeWordPressHooks(): void
    {
        $this->app->makeWith(WordPressHookService::class, ['forms' => $this->forms])
            ->registerHooks();
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
