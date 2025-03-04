<?php

namespace DigitalNode\Larafields;

use DigitalNode\Larafields\Services\WordPressHookService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
                    'pages' => $this->pages,
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
                'pages' => $this->pages,
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

    /**
     * Fetch field(s) from the database.
     */
    public static function get_field(?string $fieldKey = null, ?string $objectName = null, ?string $objectId = null): array
    {
        if ( is_null($fieldKey) && is_null($objectName) && is_null($objectId) ){
            throw new \Exception("At least one of these params is required: 'field_key', 'objectName', 'objectId' ");
        }

        $query = DB::table('larafields')
            ->when(!is_null($fieldKey), function($query) use ($fieldKey){
               $query->where('field_key', $fieldKey);
            })->when(!is_null($objectName), function($query) use ($objectName){
                $query->where('object_name', $objectName);
            })->when(!is_null($objectId), function($query) use ($objectId){
                $query->where('object_id', $objectId);
            });

        return $query
            ->get()
            ->map(fn($row) => (array) $row)
            ->map(
                fn($row) => json_validate($row['field_value']) ?
                    [...$row, 'field_value' => json_decode($row['field_value'], true)] :
                    $row
            )
            ->toArray();
    }
}
