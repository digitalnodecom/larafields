<?php

namespace DigitalNode\FormMaker\Traits;

use Illuminate\Support\Collection;
use Livewire\Livewire;

trait HandlesTermOptions
{
    /**
     * Initialize term option pages functionality.
     *
     * @return void
     */
    public function initializeTermOptions(): void
    {
        collect(get_taxonomies())->keys()->each(function($taxonomy) {
            add_filter(
                sprintf('%s_row_actions', $taxonomy), 
                [$this, 'addTermActionLinks'], 
                10, 
                2
            );
        });
    }

    /**
     * Add action links to term rows.
     *
     * @param array $links
     * @param \WP_Term $tag
     * @return array
     */
    public function addTermActionLinks(array $links, $tag): array
    {
        $this->getFormsWithTermPageConditions()->each(function($group) use (&$links, $tag) {
            $this->processTermPageConditions($group, $links, $tag);
        });

        return $links;
    }

    /**
     * Get forms that have term_page conditions configured.
     *
     * @return Collection
     */
    private function getFormsWithTermPageConditions(): Collection
    {
        return collect(config('form-maker')['forms'])->filter(function($group) {
            return collect($group['settings']['conditions'])->some(function($condition, $key) {
                return $key == 'term_page';
            });
        });
    }

    /**
     * Process term page conditions for a form group.
     *
     * @param array $group
     * @param array $links
     * @param \WP_Term $tag
     * @return void
     */
    private function processTermPageConditions(array $group, array &$links, $tag): void
    {
        collect($group['settings']['conditions'])
            ->filter(function($condition, $key) {
                return $key == 'term_page';
            })
            ->each(function($condition) use (&$links, $tag) {
                $this->addTermPageLink($condition, $links, $tag);
            });
    }

    /**
     * Add a link to the term page in the row actions.
     *
     * @param array $condition
     * @param array $links
     * @param \WP_Term $tag
     * @return void
     */
    private function addTermPageLink(array $condition, array &$links, $tag): void
    {
        if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == $condition['taxonomy']) {
            $links['mappings'] = sprintf(
                '<a href="%s">%s</a>',
                admin_url('admin.php?page=term-options&taxonomy=' . $condition['taxonomy'] . '&term_id=' . $tag->term_id),
                $condition['action_name']
            );
        }
    }

    /**
     * Create a placeholder page for term options.
     *
     * @return void
     */
    public function createPlaceholderTermOptionPage(): void
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

    /**
     * Render the term options page content.
     *
     * @return void
     */
    public function renderTermOptionsPage(): void
    {
        $taxonomy = $_GET['taxonomy'] ?? '';
        $termId = $_GET['term_id'] ?? 0;

        if (!$taxonomy || !$termId) {
            return;
        }

        collect(config('form-maker')['forms'])
            ->filter(function($group) use ($taxonomy) {
                return collect($group['settings']['conditions'])
                    ->filter(function($condition, $key) use ($taxonomy) {
                        return $key === 'term_page' && 
                               isset($condition['taxonomy']) && 
                               $condition['taxonomy'] === $taxonomy;
                    })
                    ->isNotEmpty();
            })
            ->each(function($group) use ($taxonomy, $termId) {
                echo Livewire::mount('FormMaker', [
                    'group' => $group,
                    'is_on_term_options_page' => $termId,
                    'taxonomy' => $taxonomy
                ]);
            });
    }
}
