<?php

namespace DigitalNode\FormMaker;

use Illuminate\Support\Arr;
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

    /**
     * Create a new FormMaker instance.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        add_action( 'admin_menu', array( $this, 'createMappingPages'));

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
        }, 10, 1);

        add_action('add_meta_boxes', array($this, 'renderMetaBox'));
    }

    public function renderMetaBox( $post_type ){
        collect(config('form-maker.forms'))->each(function($group) use ($post_type){
            $conditions = data_get($group, 'settings.conditions');

            collect($conditions)->contains(function($condition) use ($post_type, $group){
                if ( isset($condition['postType']) && $condition['postType'] == $post_type){
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
            });
        });
    }

    public function createMappingPages()
    {
        add_menu_page(
            'Testing Forms',
            'Testing Forms',
            'manage_woocommerce',
            'testing-forms',
            array( $this, 'renderMappingPage'),
            100
        );
    }

    public function renderMappingPage(){
        collect(config('form-maker.forms'))->each(function($group){
            echo Livewire::mount(
                'FormMaker',
                [
                    'group' => $group
                ]
            );
        });
    }
}
