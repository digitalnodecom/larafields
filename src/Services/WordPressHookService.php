<?php

namespace DigitalNode\Larafields\Services;

use Illuminate\Support\Collection;

class WordPressHookService
{
    public function __construct(
        private Collection $forms
    ) {}

    public function registerHooks(): void
    {
        $this->registerAssetHooks();
        $this->registerMetaBoxHooks();
        $this->registerMenuHooks();
        $this->registerUserHooks();
    }

    private function registerAssetHooks(): void
    {
        add_action('admin_enqueue_scripts', function (): void {
            wp_enqueue_script(
                'choices-js',
                'https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.4.1/js/tom-select.complete.js'
            );
            wp_enqueue_style(
                'choices-css',
                'https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.4.1/css/tom-select.css'
            );
        });
    }

    private function registerMetaBoxHooks(): void
    {
        add_action('add_meta_boxes', [$this, 'handleMetaBoxes']);
        add_action('wp_loaded', [$this, 'handleMetaBoxes']);
    }

    private function registerMenuHooks(): void
    {
        add_action('admin_menu', [$this, 'handleMenuPages']);
        add_filter('init', [$this, 'handleOptionPages'], 10, 2);
        add_action('admin_menu', [$this, 'createPlaceholderOptionPages']);
    }

    private function registerUserHooks(): void
    {
        add_action('edit_user_profile', [$this, 'handleUserGroups']);
        add_action('show_user_profile', [$this, 'handleUserGroups']);
    }

    public function handleMetaBoxes(string $postType): void
    {
        $this->forms->each(function (array $group) use ($postType): void {
            $conditions = collect(data_get($group, 'settings.conditions', []));

            if (isset($conditions['postType']) && $conditions['postType'] == $postType) {
                $this->addMetaBox($group, $postType);
            }

            if (isset($conditions['taxonomy'])) {
                $this->handleTaxonomyMetaBox($conditions['taxonomy'], $group);
            }
        });
    }

    private function handleTaxonomyMetaBox(string $taxonomy, array $group): void
    {
        global $pagenow;

        if ($pagenow === 'term.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] === $taxonomy) {
            add_action("{$_GET['taxonomy']}_edit_form", function () use ($group): void {
                echo app(FormRenderer::class)->renderTermMetaBox($group);
            }, 10, 2);
        }
    }

    private function addMetaBox(array $group, string $postType): void
    {
        add_meta_box(
            $group['name'],
            __($group['label'], 'larafields'),
            function () use ($group): void {
                echo app(FormRenderer::class)->renderLivewireForm($group);
            },
            $postType,
            'advanced',
            'high'
        );
    }

    public function handleUserGroups($user): void
    {
        $userGroups = $this->forms->filter(function (array $group): bool {
            $conditions = data_get($group, 'settings.conditions', []);

            return collect($conditions)->contains(function ($condition) {
                return $condition == 'user';
            });
        });

        echo app(FormRenderer::class)->renderUserGroups($userGroups, $user);
    }

    public function handleMenuPages(): void
    {
        $this->forms->each(function (array $group): void {
            $conditions = data_get($group, 'settings.conditions', []);

            if (isset($conditions['page'])) {
                $this->addMenuPage($group, $conditions['page']);
            }
        });
    }

    private function addMenuPage(array $group, array $pageConfig): void
    {
        add_menu_page(
            __($pageConfig['page_title'], 'larafields'),
            $pageConfig['menu_title'],
            'manage_options',
            $pageConfig['slug'],
            function () use ($group, $pageConfig): void {
                echo app(FormRenderer::class)->renderLivewireForm($group, [
                    'pageContext' => $pageConfig['slug'],
                ]);
            }
        );
    }

    public function handleOptionPages(): void
    {
        $formsByTaxonomy = $this->forms->reduce(function (array $grouped, array $group): array {
            $conditions = data_get($group, 'settings.conditions', []);

            if (isset($conditions['term_page']['taxonomy'])) {
                $taxonomy = $conditions['term_page']['taxonomy'];
                $grouped[$taxonomy][] = $group;
            }

            return $grouped;
        }, []);

        foreach ($formsByTaxonomy as $taxonomy => $forms) {
            add_filter(
                sprintf('%s_row_actions', $taxonomy),
                function (array $links, $tag) use ($forms): array {
                    foreach ($forms as $form) {
                        if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === $form['settings']['conditions']['term_page']['taxonomy']) {
                            $links['mappings'] = sprintf(
                                '<a href="%s">%s</a>',
                                admin_url(sprintf(
                                    'admin.php?page=lf-term-options&taxonomy=%s&term_id=%d',
                                    $form['settings']['conditions']['term_page']['taxonomy'],
                                    $tag->term_id
                                )),
                                $form['settings']['conditions']['term_page']['action_name']
                            );
                        }
                    }

                    return $links;
                },
                10,
                2
            );
        }

        $userForms = $this->forms->filter(function (array $group): bool {
            $conditions = data_get($group, 'settings.conditions', []);

            return isset($conditions['user_page']);
        })->values()->all();

        add_filter('user_row_actions', function (array $links, $user) use ($userForms): array {
            foreach ($userForms as $form) {
                $links['mappings'] = sprintf(
                    '<a href="%s">%s</a>',
                    admin_url(sprintf(
                        'admin.php?page=lf-user-options&user=%d',
                        $user->ID
                    )),
                    $form['settings']['conditions']['user_page']['action_name']
                );
            }

            return $links;
        }, 10, 2);
    }

    public function createPlaceholderOptionPages(): void
    {
        add_submenu_page(
            null,
            'Term Options Page',
            'Term Options Page',
            'manage_woocommerce',
            'lf-term-options',
            [$this, 'renderOptionPages'],
            100
        );

        add_submenu_page(
            null,
            'User Options Page',
            'User Options Page',
            'manage_woocommerce',
            'lf-user-options',
            [$this, 'renderOptionPages'],
            100
        );
    }

    public function renderOptionPages(): void
    {
        $this->forms->each(function (array $group): void {
            $conditions = data_get($group, 'settings.conditions', []);

            if (isset($conditions['user_page']) && isset($_GET['user'])) {
                echo app(FormRenderer::class)->renderLivewireForm($group, [
                    'userContext' => $_GET['user'] ?? 0,
                ]);
            }

            if (
                isset($conditions['term_page']) &&
                isset($_GET['taxonomy']) &&
                $_GET['taxonomy'] === $conditions['term_page']['taxonomy']
            ) {
                echo app(FormRenderer::class)->renderLivewireForm($group, [
                    'termOptionsContext' => $_GET['term_id'] ?? 0,
                    'taxonomyContext' => $conditions['term_page']['taxonomy'],
                ]);
            }
        });
    }
}
