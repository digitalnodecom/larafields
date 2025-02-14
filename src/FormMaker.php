<?php

namespace DigitalNode\FormMaker;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Roots\Acorn\Application;
use function Clue\StreamFilter\fun;

class FormMaker
{
    /**
     * The application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * Create a new FormMaker instance.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        add_filter('wp_head', function () {
            echo Blade::render('@livewireStyles');
        });

        add_filter('wp_footer', function () {
            echo Blade::render('@livewireScripts');
        });

        add_action('admin_enqueue_scripts', function() {
            // TODO: use local .js and .css files.
            wp_enqueue_script('choices-js', 'https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.4.1/js/tom-select.complete.js');
            wp_enqueue_style('choices-css', 'https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.4.1/css/tom-select.css');
        });

        add_action('add_meta_boxes', array($this, 'renderMetaBox'));
        add_action('wp_loaded', array($this, 'renderMetaBox'));
        add_action('admin_menu', array($this, 'addMenuPages') );
        add_filter('init', array( $this, 'addTermOptionPages' ), 10, 2);
        add_action( 'admin_menu', array( $this, 'createPlaceholderTermOptionPage'));

        add_filter('dn_form_maker_load_forms_vendor_mappings_vendor_mappings', function($field){
            $field['subfields'] = collect($field['subfields'])->map(function($subfield){
                if ( $subfield['name'] !== 'attributes' ){
                    return $subfield;
                }

                $subfield['options'] = collect(wc_get_attribute_taxonomies())->map(function($taxonomy){
                    return [
                        'value' => $taxonomy->attribute_id,
                        'label' => $taxonomy->attribute_label,
                    ];
                })->values()->toArray();

                return $subfield;
            })->values()->toArray();

            return $field;
        });
    }

    public function renderMetaBox( $post_type ){
        collect(config('form-maker.forms'))->each(function($group) use ($post_type){
            $conditions = data_get($group, 'settings.conditions');

            collect($conditions)->contains(function($condition) use ($post_type, $group){
                if ( isset($condition['postType']) && $condition['postType'] == $post_type){
                    $this->renderMetaBoxForGroup($group, $post_type);
                    return;
                }

                global $pagenow;

                if ( $pagenow == 'term.php' && isset($condition['taxonomy']) && $condition['taxonomy'] == $_GET['taxonomy']) {
                    add_action("{$_GET['taxonomy']}_edit_form", [$this, 'renderTermMetaBox'], 10, 2);
                    return;
                }
            });
        });
    }

    private function renderMetaBoxForGroup( $group, $post_type ) {
        add_meta_box(
            $group['name'],
            __( $group['label'], 'formmaker' ),
            function() use ($group){
                echo Livewire::mount(
                    'FormMaker',
                    [
                        'group' => $group
                    ]
                );
            },
            $post_type,
            'advanced',
            'high'
        );
    }

    public function renderTermMetaBox(){
        $termGroups = collect(config('form-maker.forms'))->filter(function($group){
            return collect(data_get($group, 'settings.conditions'))->filter(function($condition){
                return array_key_first($condition) == 'taxonomy' && $condition['taxonomy'] == $_GET['taxonomy'];
            })->isNotEmpty();
        });

        $termGroups->each(function($group){
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

    public function addMenuPages(){
        $formsForPages = collect(config('form-maker')['forms'])->filter(function($group){
            return collect($group['settings']['conditions'])->some(function($condition, $key){
                return 'page' == $key;
            });
        });

        $formsForPages->each(function($group){
            collect($group['settings']['conditions'])->filter(function($condition, $key){
                return 'page' == $key;
            })->each(function($condition) use ($group){
                add_menu_page(
                    __( $condition['page_title'], 'form-maker' ),
                    $condition['menu_title'],
                    'manage_options',
                    $condition['slug'],
                    function() use ($group, $condition){
                        echo Livewire::mount(
                            'FormMaker',
                            [
                                'group' => $group,
                                'is_on_page' => $condition['slug']
                            ]
                        );
                    },
                );
            });
        });
    }

    public function addTermOptionPages($options){
        collect(get_taxonomies())->keys()->each(function($taxonomy){
            add_filter( sprintf('%s_row_actions', $taxonomy), array( $this, 'appendTermOptionLinks' ), 10, 2);
        });
    }

    public function appendTermOptionLinks($links, $tag){
        $formsForPages = collect(config('form-maker')['forms'])->filter(function($group){
            return collect($group['settings']['conditions'])->some(function($condition, $key){
                return $key == 'term_page';
            });
        });

        $formsForPages->each(function($group) use (&$links, $tag){
            collect($group['settings']['conditions'])->filter(function($condition, $key){
                return $key == 'term_page';
            })->each(function($condition,) use ($group, &$links, $tag){
                if ( isset($_GET['taxonomy']) && $_GET['taxonomy'] == $condition['taxonomy'] ){
                    $links['mappings'] = sprintf(
                        '<a href="%s">%s</a>',
                        admin_url('admin.php?page=term-options&taxonomy='. $condition['taxonomy'] . '&term_id=' . $tag->term_id),
                        $condition['action_name']
                    );
                }
            });
        });

        return $links;
    }

    public function createPlaceholderTermOptionPage(){
        add_submenu_page(
            null,
            'Term Options Page',
            'Term Options Page',
            'manage_woocommerce',
            'term-options',
            array( $this, 'renderTermOptionsPage'),
            100
        );
    }

    public function renderTermOptionsPage(){
        collect(config('form-maker')['forms'])->filter(function($group){
            return collect($group['settings']['conditions'])->where(function($value, $key) use ($group){
                if ( isset($_GET['taxonomy']) && $key == 'term_page' && $value['taxonomy'] == $_GET['taxonomy'] ){
                    echo Livewire::mount(
                        'FormMaker',
                        [
                            'group' => $group,
                            'is_on_term_options_page' => $_GET['term_id'] ?? 0,
                            'taxonomy' => $value['taxonomy']
                        ]
                    );
                }
            });
        });
    }

    public static function add_group($data){
       $forms = config('form-maker.forms');

       $forms[] = $data;

       config(['form-maker.forms' => $forms]);
    }
}
