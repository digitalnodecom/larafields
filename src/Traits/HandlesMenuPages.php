<?php

namespace DigitalNode\FormMaker\Traits;

use Illuminate\Support\Collection;
use Livewire\Livewire;

trait HandlesMenuPages
{
    /**
     * Add menu pages for forms configured with page conditions.
     *
     * @return void
     */
    public function addMenuPages(): void
    {
        $this->getFormsWithPageConditions()->each(function($group) {
            $this->processPageConditions($group);
        });
    }

    /**
     * Get forms that have page conditions configured.
     *
     * @return Collection
     */
    private function getFormsWithPageConditions(): Collection
    {
        return collect(config('form-maker')['forms'])->filter(function($group) {
            return collect($group['settings']['conditions'])->some(function($condition, $key) {
                return 'page' == $key;
            });
        });
    }

    /**
     * Process page conditions for a form group and add menu pages.
     *
     * @param array $group
     * @return void
     */
    private function processPageConditions(array $group): void
    {
        collect($group['settings']['conditions'])
            ->filter(function($condition, $key) {
                return 'page' == $key;
            })
            ->each(function($condition) use ($group) {
                $this->addMenuPage($group, $condition);
            });
    }

    /**
     * Add a single menu page.
     *
     * @param array $group
     * @param array $condition
     * @return void
     */
    private function addMenuPage(array $group, array $condition): void
    {
        add_menu_page(
            __($condition['page_title'], 'form-maker'),
            $condition['menu_title'],
            'manage_options',
            $condition['slug'],
            function() use ($group, $condition) {
                echo Livewire::mount('FormMaker', [
                    'group' => $group,
                    'is_on_page' => $condition['slug']
                ]);
            }
        );
    }
}
