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
