<?php

namespace DigitalNode\Larafields\Services;

use Illuminate\Support\Collection;
use function Crontrol\Schedule\add;
use function PHPUnit\Framework\matches;
use DigitalNode\Larafields\Services\FormRenderer;

class WordPressHookService
{
    public function __construct(
        private Collection $forms,
        private Collection $pages
    ) {
    }

    public function registerHooks(): void
    {
        $this->registerAssetHooks();
        $this->registerMetaBoxHooks();
        $this->registerUserHooks();
    }

    public function registerAdminMenuHooks(): void
    {
        $this->handleOptionPages();
        $this->handleOptionPagesActionLinks();
        $this->handleMenuPages();
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
        $this->pages->each(function (array $page): void {
            add_menu_page(
                page_title: $page['page_title'],
                menu_title: $page['menu_title'],
                capability: 'manage_woocommerce',
                menu_slug: $page['slug'],
                callback: '__return_false'
            );

            if (isset($page['hide_from_submenu']) && $page['hide_from_submenu']) {
                add_action('admin_menu', function () use ($page) {
                    global $submenu;
                    if (isset($submenu[$page['slug']])) {
                        unset($submenu[$page['slug']][0]);
                    }
                }, 999);
            }
        });

        $this->forms->each(function (array $group): void {
            $conditions = data_get($group, 'settings.conditions', []);

            if (isset($conditions['page'])) {
                $this->addMenuPage($group, $conditions['page']);
            }
        });
    }

    private function addMenuPage(array $group, array $pageConfig): void
    {
        $addMenuOrSubmenuFunction = 'add_menu_page';

        $args = [
            'page_title' => __($pageConfig['page_title'], 'larafields'),
            'menu_title' => $pageConfig['menu_title'],
            'capability' => 'manage_woocommerce',
            'menu_slug' => $pageConfig['slug'],
            'callback' => function () use ($group, $pageConfig): void {
                echo app(FormRenderer::class)->renderLivewireForm($group, [
                    'pageContext' => $pageConfig['slug'],
                ]);
            }
        ];

        if (isset($pageConfig['parent'])) {
            $addMenuOrSubmenuFunction = 'add_submenu_page';

            $args['parent_slug'] = $pageConfig['parent'];
        }

        call_user_func_array($addMenuOrSubmenuFunction, $args);
    }

    public function handleOptionPagesActionLinks(): void
    {
        $this->forms->each(function (array $group) {
            $conditions = data_get($group, 'settings.conditions', []);

            if (isset($conditions['term_page']['taxonomy'])) {
                $taxonomy = $conditions['term_page']['taxonomy'];

                add_filter(
                    sprintf('%s_row_actions', $taxonomy),
                    function (array $links, $tag) use ($conditions): array {
                        $termPageCondition = $conditions['term_page'];

                        $links[$termPageCondition['slug']] = sprintf(
                            '<a href="%s">%s</a>',
                            url()->query(
                                menu_page_url($termPageCondition['slug'], false),
                                [
                                    'term_id' => $tag->term_id,
                                    'taxonomy' => $termPageCondition['taxonomy'],
                                ]
                            ),
                            $termPageCondition['action_name']
                        );

                        return $links;
                    },
                    10,
                    2
                );
            }

            if (isset($conditions['user_page'])) {
                add_filter('user_row_actions', function (array $links, $user) use ($group): array {
                    $userPageCondition = $group['settings']['conditions']['user_page'];

                    $links[$userPageCondition['slug']] = sprintf(
                        '<a href="%s">%s</a>',
                        url()->query(
                            menu_page_url($userPageCondition['slug'], false),
                            ['user' => $user->ID]
                        ),
                        $userPageCondition['action_name']
                    );

                    return $links;
                }, 10, 2);
            }
        });
    }

    public function handleOptionPages(): void
    {
        collect($this->forms)->each(function ($form) {
            collect($form['settings']['conditions'] ?? [])
                ->filter(fn($value, $key) => in_array($key, ['term_page', 'user_page', 'page']))
                ->each(function ($pageCondition, $pageConditionKey) use ($form) {
                    add_submenu_page(
                        null,
                        $pageCondition['page_title'],
                        $pageCondition['menu_title'],
                        'manage_woocommerce',
                        $pageCondition['slug'],
                        function () use ($form, $pageCondition, $pageConditionKey) {
                            $componentArgs = [
                                'userContext' => $_GET['user'] ?? 0,
                            ];

                            if ($pageConditionKey == 'term_page') {
                                $componentArgs = [
                                    'termOptionsContext' => $_GET['term_id'] ?? 0,
                                    'taxonomyContext' => $pageCondition['taxonomy'],
                                ];
                            }

                            echo app(FormRenderer::class)->renderLivewireForm($form, $componentArgs);
                        },
                        100
                    );
                });
        });
    }
}
