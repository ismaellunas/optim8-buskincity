<?php

namespace App\Services;

use App\Models\{
    Category,
    Media,
    Menu,
    MenuItem,
    Page,
    PageTranslation,
    Post,
    Role,
    User,
};
use Illuminate\Http\Request;
use App\Entities\Caches\{
    MenuCache,
    SettingCache,
};

class MenuService
{
    public function getHeaderMenus(array $locales = []): array
    {
        $menus = [];

        $locales = array_merge([config('app.fallback_locale')], $locales);

        foreach ($locales as $locale) {
            $menus[$locale] = Menu::generateMenuItems($locale);
        }

        return $menus;
    }

    public function getHeaderMenu(string $locale): array
    {
        return app(MenuCache::class)->remember(
            'header_menu',
            function () use ($locale) {
                $menu = Menu::header()
                    ->locale($locale)
                    ->with([
                        'menuItems' => function ($query) {
                            $query
                                ->orderBy('order', 'ASC')
                                ->orderBy('parent_id', 'ASC')
                                ->with([
                                    'post' => function ($query) {
                                        $query->select([
                                            'id',
                                            'slug',
                                        ]);
                                    },
                                    'page' => function ($query) {
                                        $query->select('id');
                                        $query->with('translations', function ($query) {
                                            $query->select([
                                                'id',
                                                'page_id',
                                                'locale',
                                                'slug',
                                            ]);
                                        });
                                    },
                                ]);
                        },
                    ])
                    ->first();

                return $menu ? Menu::createArrayMenuItems($menu) : [];
            },
            $locale
        );
    }

    public function getFooterMenus(array $locales = []): array
    {
        $menus = [];

        $locales = array_merge([config('app.fallback_locale')], $locales);

        foreach ($locales as $locale) {
            $menus[$locale] = Menu::generateMenuItems($locale, 2);
        }

        return $menus;
    }

    public function getFooterMenu(string $locale): array
    {
        return app(MenuCache::class)->remember(
            'footer_menu',
            function () use ($locale) {
                $menu = Menu::footer()
                    ->locale($locale)
                    ->with([
                        'menuItems' => function ($query) {
                            $query
                                ->orderBy('order', 'ASC')
                                ->orderBy('parent_id', 'ASC')
                                ->with([
                                    'post' => function ($query) {
                                        $query->select([
                                            'id',
                                            'slug',
                                        ]);
                                    },
                                    'page' => function ($query) {
                                        $query->select('id');
                                        $query->with('translations', function ($query) {
                                            $query->select([
                                                'id',
                                                'page_id',
                                                'locale',
                                                'slug',
                                            ]);
                                        });
                                    },
                                ]);
                        },
                    ])
                    ->first();

                return $menu ? Menu::createArrayMenuItems($menu) : [];
            },
            $locale
        );
    }

    public function getSocialMediaMenus(): array
    {
        return app(SettingCache::class)->remember(
            'social_media',
            function () {
                $menu = Menu::with([
                    'menuItems' => function ($query) {
                            $query->select([
                                    'id',
                                    'url',
                                    'icon',
                                    'is_blank',
                                    'menu_id',
                                ]);
                            $query->where('type', MenuItem::TYPE_URL);
                        }
                    ])
                    ->socialMedia()
                    ->first();

                return $menu
                    ? $menu->menuItems
                        ->each(function ($menuItem) {
                            $menuItem->target = $menuItem->is_blank ? 'is_blank' : null;
                        })
                        ->toArray()
                    : [];
            }
        );
    }

    public static function generateBackendMenu(Request $request): array
    {
        $user = $request->user();

        $menuLogo = [
            'title' => 'Dashboard',
            'link' => route('dashboard'),
        ];

        $menus = [
            'dashboard' => [
                'title' => 'Dashboard',
                'link' => route('dashboard'),
                'isActive' => $request->routeIs('dashboard'),
                'isEnabled' => true,
            ]
        ];

        if ($user->can('system.dashboard') && $request->routeIs('admin.*')) {
            $menuLogo = [
                'title' => 'Dashboard',
                'link' => route('admin.dashboard'),
            ];

            $menus = [
                [
                    'title' => 'Dashboard',
                    'link' => route('admin.dashboard'),
                    'isActive' => $request->routeIs('admin.dashboard'),
                    'isEnabled' => true,
                ],
                [
                    'title' => 'Pages',
                    'link' => route('admin.pages.index'),
                    'isActive' => $request->routeIs('admin.pages.*'),
                    'isEnabled' => $user->can('viewAny', Page::class),
                ],
                [
                    'title' => 'Blog',
                    'isActive' => (
                        $request->routeIs('admin.posts.*')
                        || $request->routeIs('admin.categories.*')
                    ),
                    'isEnabled' => (
                        $user->can('viewAny', Post::class)
                        || $user->can('viewAny', Category::class)
                    ),
                    'children' => [
                        [
                            'title' => 'Posts',
                            'link' => route('admin.posts.index'),
                            'isActive' => $request->routeIs('admin.posts.*'),
                            'isEnabled' => $user->can('viewAny', Post::class),
                        ],
                        [
                            'title' => 'Categories',
                            'link' => route('admin.categories.index'),
                            'isActive' => $request->routeIs('admin.categories.*'),
                            'isEnabled' => $user->can('viewAny', Category::class),
                        ],
                    ],
                ],
                [
                    'title' => 'Media',
                    'link' => route('admin.media.index'),
                    'isActive' => $request->routeIs('admin.media.*'),
                    'isEnabled' => $user->can('viewAny', Media::class),
                ],
                [
                    'title' => 'Theme',
                    'isActive' => $request->routeIs('admin.theme.*'),
                    'isEnabled' => $user->can('system.theme'),
                    'children' => [
                        [
                            'title' => 'Header',
                            'link' => route('admin.theme.header.edit'),
                            'isActive' => $request->routeIs('admin.theme.header.*'),
                            'isEnabled' => true,
                        ],
                        [
                            'title' => 'Footer',
                            'link' => route('admin.theme.footer.edit'),
                            'isActive' => $request->routeIs('admin.theme.footer.*'),
                            'isEnabled' => true,
                        ],
                        [
                            'title' => 'Colors',
                            'link' => route('admin.theme.color.edit'),
                            'isActive' => $request->routeIs('admin.theme.color.*'),
                            'isEnabled' => true,
                        ],
                        [
                            'title' => 'Fonts',
                            'link' => route('admin.theme.fonts.edit'),
                            'isActive' => $request->routeIs('admin.theme.fonts.*'),
                            'isEnabled' => true,
                        ],
                        [
                            'title' => 'Font Sizes',
                            'link' => route('admin.theme.font-size.edit'),
                            'isActive' => $request->routeIs('admin.theme.font-size.*'),
                            'isEnabled' => true,
                        ],
                        [
                            'title' => 'Advanced',
                            'link' => route('admin.theme.advance.edit'),
                            'isActive' => $request->routeIs('admin.theme.advance.*'),
                            'isEnabled' => true,
                        ],
                    ],
                ],
                [
                    'title' => 'Settings',
                    'isActive' => $request->routeIs('admin.setting.*'),
                    'isEnabled' => $user->can('system.language'),
                    'children' => [
                        [
                            'title' => 'Languages',
                            'link' => route('admin.settings.languages.edit'),
                            'isActive' => $request->routeIs('admin.settings.languages.edit'),
                            'isEnabled' => $user->can('system.language'),
                        ],
                        [
                            'title' => 'Translation Manager',
                            'link' => route('admin.settings.translation-manager.edit'),
                            'isActive' => $request->routeIs('admin.settings.translation-manager.edit'),
                            'isEnabled' => $user->can('system.translation'),
                        ],
                    ],
                ],
                [
                    'title' => 'Users',
                    'isActive' => $request->routeIs('admin.users.*') || $request->routeIs('admin.roles.*'),
                    'isEnabled' => $user->can('viewAny', User::class) || $user->can('viewAny', Role::class),
                    'children' => [
                        [
                            'title' => 'All Users',
                            'link' => route('admin.users.index'),
                            'isActive' => $request->routeIs('admin.users.*'),
                            'isEnabled' => $user->can('viewAny', User::class),
                        ],
                        [
                            'title' => 'Roles',
                            'link' => route('admin.roles.index'),
                            'isActive' => $request->routeIs('admin.roles.*'),
                            'isEnabled' => $user->can('viewAny', Role::class),
                        ],
                    ],
                ],
            ];

            $menuProfile = [
                'title' => 'Profile',
                'link' => route('admin.profile.show'),
            ];
        } else {
            $menuProfile = [
                'title' => 'Profile',
                'link' => route('user.profile.show'),
            ];
        }

        return [
            'nav' => $menus,
            'navLogo' => $menuLogo,
            'navProfile' => $menuProfile,
        ];
    }

    public function getPageOptions(): array
    {
        return Page::with([
                'translations' => function ($query) {
                    $query->select([
                        'id',
                        'page_id',
                        'locale',
                        'title',
                    ])
                    ->published();
                },
            ])
            ->get(['id'])
            ->map(function ($page) {
                if (count($page->translations) !== 0) {
                    $locales = $page
                        ->translations
                        ->map(function ($translation) {
                            return $translation->locale;
                        });
                    return [
                        'id' => $page->id,
                        'value' => $page->title ?? $page->translations[0]->title,
                        'locales' => $locales,
                    ];
                }
            })->filter(function ($value) {
                return $value != null;
            })
            ->values()
            ->all();
    }

    public function getPostOptions(): array
    {
        return Post::published()
            ->get([
                'id',
                'locale',
                'title',
            ])
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'value' => $post->title,
                    'locale' => $post->locale,
                ];
            })
            ->all();
    }

    public function getCategoryOptions(): array
    {
        return Category::with([
                'translations' => function ($query) {
                    $query->select([
                        'id',
                        'category_id',
                        'locale',
                        'name',
                    ]);
                },
            ])
            ->get(['id'])
            ->map(function ($category) {

                $locales = $category
                    ->translations
                    ->map(function ($translation) {
                        return $translation->locale;
                    });

                return [
                    'id' => $category->id,
                    'value' => $category->name ?? $category->translations[0]->name,
                    'locales' => $locales,
                ];
            })
            ->all();
    }

    public function getMenuItemTypeOptions(): array
    {
        return collect(MenuItem::TYPE_VALUES)
            ->map(function ($item, $key) {
                return [
                    'id' => $key,
                    'value' => $item,
                ];
            })
            ->all();
    }

    public function isPageUsedInMenus($menus, $page_id)
    {
        $isPageUsed = [];

        foreach ($menus as $locale => $menuItems) {
            $isPageUsed[$locale] = false;
            foreach ($menuItems as $menuItem) {
                if ($menuItem->page_id === $page_id) {
                    $isPageUsed[$locale] = true;
                }
            }
        }

        return $isPageUsed;
    }

    public function removePageFromMenus(array $inputs)
    {
        $languages = TranslationService::getLocales();
        $headerMenuItems = $this->getHeaderMenus($languages);
        $footerMenuItems = $this->getFooterMenus($languages);
        foreach ($inputs as $locale => $input) {
            if (count($input) > 0 && isset($input['page_id'])) {
                $this->removeMenuItems($headerMenuItems, $locale, $input['page_id'], $input['status']);
                $this->removeMenuItems($footerMenuItems, $locale, $input['page_id'], $input['status']);
            }
        }
        app(MenuCache::class)->flush();
    }

    private function removeMenuItems(
        array $menuItems,
        string $locale,
        int $pageId,
        int $pageStatus
    ) {
        foreach($menuItems[$locale] as $menuItem) {
            if ($pageId === $menuItem['page_id']) {
                if ($pageStatus === PageTranslation::STATUS_DRAFT) {
                    $menus = Menu::with(['menuItems' => function($query) use($pageId){
                        $query->where('page_id', $pageId);
                    }])
                    ->where('locale', $locale)->get();
                    foreach ($menus as $menu) {
                        if (count($menu->menuItems) > 0) {
                            $menu->menuItems[0]->delete();
                        }
                    }
                }
            }
        }
    }
}
