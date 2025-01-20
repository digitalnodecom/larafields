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
            wp_enqueue_script('choices-js', 'https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.4.1/js/tom-select.complete.js');
            wp_enqueue_style('choices-css', 'https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.4.1/css/tom-select.css');
        }, 10, 1);
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
