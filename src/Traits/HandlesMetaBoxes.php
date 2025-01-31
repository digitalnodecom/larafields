<?php

namespace DigitalNode\FormMaker\Traits;

use Livewire\Livewire;

trait HandlesMetaBoxes
{
    /**
     * Initialize meta boxes for post types and taxonomies.
     *
     * @param string|null $post_type
     * @return void
     */
    public function initializeMetaBoxes(?string $post_type = null): void
    {
        collect(config('form-maker.forms'))->each(function($group) use ($post_type) {
            $conditions = data_get($group, 'settings.conditions');

            collect($conditions)->contains(function($condition) use ($post_type, $group) {
                if (isset($condition['postType']) && $condition['postType'] == $post_type) {
                    $this->addMetaBoxForGroup($group, $post_type);
                    return true;
                }

                global $pagenow;
                if ($pagenow == 'term.php' && isset($condition['taxonomy']) && $condition['taxonomy'] == $_GET['taxonomy'] ?? '') {
                    add_action("{$_GET['taxonomy']}_edit_form", [$this, 'renderTermMetaBox'], 10, 2);
                    return true;
                }

                return false;
            });
        });
    }

    /**
     * Add meta box for a specific form group.
     *
     * @param array $group
     * @param string $post_type
     * @return void
     */
    private function addMetaBoxForGroup(array $group, string $post_type): void
    {
        add_meta_box(
            $group['name'],
            __($group['label'], 'formmaker'),
            function() use ($group) {
                echo Livewire::mount('FormMaker', ['group' => $group]);
            },
            $post_type,
            'advanced',
            'high'
        );
    }

    /**
     * Render meta box for taxonomy terms.
     *
     * @return void
     */
    public function renderTermMetaBox(): void
    {
        $termGroups = $this->getTermGroups();

        $termGroups->each(function($group) {
            echo view('form-maker::components.term-meta-box', [
                'group' => $group,
                'content' => Livewire::mount('FormMaker', ['group' => $group])
            ]);
        });
    }

    /**
     * Get form groups configured for the current taxonomy.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getTermGroups()
    {
        return collect(config('form-maker.forms'))->filter(function($group) {
            return collect(data_get($group, 'settings.conditions'))->filter(function($condition) {
                return array_key_first($condition) == 'taxonomy' 
                    && $condition['taxonomy'] == $_GET['taxonomy'] ?? '';
            })->isNotEmpty();
        });
    }
}
