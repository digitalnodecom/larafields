<?php

namespace DigitalNode\FormMaker;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Roots\Acorn\Application;

class FormMaker
{
    /**
     * The application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    protected Collection $forms;

    /**
     * Create a new FormMaker instance.
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->forms = collect(
            apply_filters('larafields_load_forms', config('form-maker.forms', []))
        );

        add_filter('wp_head', function () {
            echo Blade::render('@livewireStyles');
        });

        add_filter('wp_footer', function () {
            echo Blade::render('@livewireScripts');
        });

        add_action('admin_enqueue_scripts', function () {
            // TODO: use local .js and .css files.
            wp_enqueue_script('choices-js', 'https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.4.1/js/tom-select.complete.js');
            wp_enqueue_style('choices-css', 'https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.4.1/css/tom-select.css');
        });

        add_action('add_meta_boxes', [$this, 'renderMetaBox']);
        add_action('wp_loaded', [$this, 'renderMetaBox']);
        add_action('admin_menu', [$this, 'addMenuPages']);
        add_filter('init', [$this, 'addTermOptionPages'], 10, 2);
        add_action('admin_menu', [$this, 'createPlaceholderTermOptionPage']);
    }

    public function renderMetaBox($post_type)
    {
        $this->forms->each(function ($group) use ($post_type) {
            $conditions = data_get($group, 'settings.conditions');

            collect($conditions)->contains(function ($condition) use ($post_type, $group) {
                if (isset($condition['postType']) && $condition['postType'] == $post_type) {
                    $this->renderMetaBoxForGroup($group, $post_type);

                    return;
                }

                global $pagenow;

                if ($pagenow == 'term.php' && isset($condition['taxonomy']) && $condition['taxonomy'] == $_GET['taxonomy']) {
                    add_action("{$_GET['taxonomy']}_edit_form", [$this, 'renderTermMetaBox'], 10, 2);

                    return;
                }
            });
        });
    }

    private function renderMetaBoxForGroup($group, $post_type)
    {
        add_meta_box(
            $group['name'],
            __($group['label'], 'formmaker'),
            function () use ($group) {
                echo Livewire::mount(
                    'FormMaker',
                    [
                        'group' => $group,
                    ]
                );
            },
            $post_type,
            'advanced',
            'high'
        );
    }

    public function renderTermMetaBox()
    {
        $termGroups = $this->forms->filter(function ($group) {
            return collect(data_get($group, 'settings.conditions'))->filter(function ($condition) {
                return array_key_first($condition) == 'taxonomy' && $condition['taxonomy'] == $_GET['taxonomy'];
            })->isNotEmpty();
        });

        $termGroups->each(function ($group) {
            ?>
            <div id="poststuff">
                <div class="form-field postbox">
                    <div class="postbox-header">
                        <h2><?= $group['label'] ?></h2>
                    </div>
                    <div class="inside">
                        <?php
                        echo Livewire::mount(
                            'FormMaker',
                            [
                                'group' => $group,
                            ]
                        );
            ?>
                    </div>
                </div>
            </div>
            <?php
        });
    }

    public function addMenuPages()
    {
        $formsForPages = $this->forms->filter(function ($group) {
            return collect($group['settings']['conditions'])->some(function ($condition, $key) {
                return $key == 'page';
            });
        });

        $formsForPages->each(function ($group) {
            collect($group['settings']['conditions'])->filter(function ($condition, $key) {
                return $key == 'page';
            })->each(function ($condition) use ($group) {
                add_menu_page(
                    __($condition['page_title'], 'form-maker'),
                    $condition['menu_title'],
                    'manage_options',
                    $condition['slug'],
                    function () use ($group, $condition) {
                        echo Livewire::mount(
                            'FormMaker',
                            [
                                'group' => $group,
                                'is_on_page' => $condition['slug'],
                            ]
                        );
                    },
                );
            });
        });
    }

    public function addTermOptionPages($options)
    {
        collect(get_taxonomies())->keys()->each(function ($taxonomy) {
            add_filter(sprintf('%s_row_actions', $taxonomy), [$this, 'appendTermOptionLinks'], 10, 2);
        });
    }

    public function appendTermOptionLinks($links, $tag)
    {
        $formsForPages = $this->forms->filter(function ($group) {
            return collect($group['settings']['conditions'])->some(function ($condition, $key) {
                return $key == 'term_page';
            });
        });

        $formsForPages->each(function ($group) use (&$links, $tag) {
            collect($group['settings']['conditions'])->filter(function ($condition, $key) {
                return $key == 'term_page';
            })->each(function ($condition) use (&$links, $tag) {
                if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == $condition['taxonomy']) {
                    $links['mappings'] = sprintf(
                        '<a href="%s">%s</a>',
                        admin_url('admin.php?page=term-options&taxonomy='.$condition['taxonomy'].'&term_id='.$tag->term_id),
                        $condition['action_name']
                    );
                }
            });
        });

        return $links;
    }

    public function createPlaceholderTermOptionPage()
    {
        add_submenu_page(
            null,
            'Term Options Page',
            'Term Options Page',
            'manage_woocommerce',
            'term-options',
            [$this, 'renderTermOptionsPage'],
            100
        );
    }

    public function renderTermOptionsPage()
    {
        $this->forms->filter(function ($group) {
            return collect($group['settings']['conditions'])->where(function ($value, $key) use ($group) {
                if (isset($_GET['taxonomy']) && $key == 'term_page' && $value['taxonomy'] == $_GET['taxonomy']) {
                    echo Livewire::mount(
                        'FormMaker',
                        [
                            'group' => $group,
                            'is_on_term_options_page' => $_GET['term_id'] ?? 0,
                            'taxonomy' => $value['taxonomy'],
                        ]
                    );
                }
            });
        });
    }

    public static function add_group($data)
    {
        $forms = config('form-maker.forms');

        $forms[] = $data;

        config(['form-maker.forms' => $forms]);
    }
}
