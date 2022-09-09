<?php

namespace App\Services;

use App\Models\{
    Category,
    Media,
    Menu,
    MenuItem,
    Page,
    Post,
    Role,
    User,
};
use App\Services\{
    LanguageService,
    LoginService,
    TranslationService,
    ModuleService,
};
use Illuminate\Http\Request;
use App\Entities\Caches\{
    MenuCache,
    SettingCache,
};
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class MenuService
{
    public function getHeaderMenus(array $locales = []): array
    {
        $menus = [];

        $locales = array_merge([config('app.fallback_locale')], $locales);

        foreach ($locales as $locale) {
            $menus[$locale] = $this->menuArrayFormatter(
                $this->getStructuredHeaderMenu($locale)
            );
        }

        return $menus;
    }

    private function getTypeMenuClass(string $type)
    {
        $typeValues = MenuItem::getAllTypeValues();

        return "\\App\\Entities\\Menus\\".$typeValues[$type]."Menu";
    }

    private function createStructuredMenus(
        Collection $menuItems,
        string $locale,
        ?int $parentId = null
    ): Collection {
        $menus = collect();

        $menuItems
            ->where('parent_id', '==', $parentId)
            ->sortBy('order')
            ->each(function ($menuItem) use ($menus, $menuItems, $locale) {
                $className = $this->getTypeMenuClass($menuItem->type);
                $menu = new $className($menuItem, $locale);

                if ($menuItems->contains('parent_id', $menuItem->id)) {
                    $menu->children = $this->createStructuredMenus(
                        $menuItems,
                        $locale,
                        $menuItem->id
                    );
                }

                $menus->push($menu);
            });

        return $menus;
    }

    private function getStructuredHeaderMenu(string $locale): Collection
    {
        $menu = Menu::header()
            ->locale($locale)
            ->menuItemsBuilder()
            ->first();

        if ($menu) {
            return $this->createStructuredMenus($menu->menuItems, $menu->locale);
        } else {
            return collect();
        }
    }

    public function getHeaderMenu(string $locale): Collection
    {
        return app(MenuCache::class)->rememberForLocale(
            'header_menu',
            function () use ($locale) {
                return $this->getStructuredHeaderMenu($locale);
            },
            $locale
        );
    }

    private function menuArrayFormatter(Collection $typedMenus)
    {
        $menus = [];

        foreach ($typedMenus as $menu) {
            $menuItem = $menu->getModel();
            $children = $menu->children ?? collect([]);

            $this->menuItemArrayFormater(
                $menuItem,
                $menu,
                $this->menuArrayFormatter($children)
            );

            $menus[] = $menuItem;
        }

        return $menus;
    }

    private function frontendMenuArrayFormater(Collection $typedMenus): array
    {
        $menus = [];

        foreach ($typedMenus as $menu) {
            $menuItem = [];
            $children = $menu->children ?? collect([]);

            $this->menuItemArrayFormater(
                $menuItem,
                $menu,
                $this->frontendMenuArrayFormater($children)
            );

            $menus[] = $menuItem;
        }

        return $menus;
    }

    private function menuItemArrayFormater(
        &$menuItem,
        $menu,
        $children
    ): void {
        $menuItem['title'] = $menu->title;
        $menuItem['link'] = $menu->getUrl();
        $menuItem['target'] = $menu->getTarget();
        $menuItem['isActive'] = $menu->isActive(request()->url());
        $menuItem['isInternalLink'] = $menu->isInternalLink($menuItem['link']);
        $menuItem['children'] = $children;
    }

    public function getFooterMenus(array $locales = []): array
    {
        $menus = [];

        $locales = array_merge([config('app.fallback_locale')], $locales);

        foreach ($locales as $locale) {
            $menus[$locale] = $this->menuArrayFormatter(
                $this->getStructuredFooterMenu($locale)
            );
        }

        return $menus;
    }

    private function getStructuredFooterMenu(string $locale): Collection
    {
        $menu = Menu::footer()
            ->locale($locale)
            ->menuItemsBuilder()
            ->first();

        if ($menu) {
            return $this->createStructuredMenus($menu->menuItems, $menu->locale);
        } else {
            return collect();
        }
    }

    public function getFooterMenu(string $locale): Collection
    {
        return app(MenuCache::class)->rememberForLocale(
            'footer_menu',
            function () use ($locale) {
                return $this->getStructuredFooterMenu($locale);
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

    public function getBackendNavMenus(Request $request): array
    {
        $user = $request->user();

        $menuLogo = [
            'title' => 'Dashboard',
            'link' => route('dashboard'),
        ];

        if ($request->routeIs('admin.*')) {
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
                        [
                            'title' => 'Stripe',
                            'link' => route('admin.settings.stripe.edit'),
                            'isActive' => $request->routeIs('admin.settings.stripe.edit'),
                            'isEnabled' => $user->can('system.payment'),
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

            $moduleMenus = $this->moduleMenus($request);

            $menuProfile = [
                'title' => 'Profile',
                'link' => route('admin.profile.show'),
            ];

        } else {

            $menus = [
                [
                    'title' => 'Dashboard',
                    'link' => route('dashboard'),
                    'isActive' => $request->routeIs('dashboard'),
                    'isEnabled' => true,
                ],
            ];

            $moduleMenus = [];

            $menuProfile = [
                'title' => 'Profile',
                'link' => route('user.profile.show'),
            ];
        }

        return [
            'nav' => array_merge($menus, $moduleMenus),
            'navLogo' => $menuLogo,
            'navProfile' => $menuProfile,
        ];
    }

    public function getFrontendUserMenus(Request $request): array
    {
        $user = $request->user();

        $dropdownRightMenus = [];
        $language = app(LanguageService::class)->getOriginLanguageFromCookie();

        if ($user) {
            $language =  $user->languageCode;

            if (LoginService::isAdminHomeUrl()) {
                $dropdownRightMenus = [
                    [
                        'title' => 'Dashboard',
                        'link' => route('admin.dashboard'),
                        'isEnabled' => true,
                    ],
                    [
                        'title' => 'Profile',
                        'link' => route('admin.profile.show'),
                        'isEnabled' => true,
                    ],
                ];
            } else {
                $dropdownRightMenus = [
                    [
                        'title' => 'Dashboard',
                        'link' => route('dashboard'),
                        'isEnabled' => true,
                    ],
                    [
                        'title' => 'Payments',
                        'link' => route('payments.index'),
                        'isEnabled' => $user->can('payment.management'),
                    ],
                ];

                $moduleRightMenus = $this->moduleMenus($request, 'frontend');

                foreach ($moduleRightMenus as $frontendRightMenus) {
                    foreach ($frontendRightMenus as $menu) {
                        $dropdownRightMenus[] = $menu;
                    }
                }

                $dropdownRightMenus[] = [
                    'title' => 'Profile',
                    'link' => route('user.profile.show'),
                    'isEnabled' => true,
                ];
            }
        }

        $menuLogo = [
            'title' => 'Homepage',
            'link' => LaravelLocalization::localizeURL(
                route('homepage'),
                $language
            ),
        ];

        $headerMenu = $this->getHeaderMenu($language);

        if ($headerMenu->isEmpty()) {
            $headerMenu = $this->getHeaderMenu(
                app(TranslationService::class)->getDefaultLocale()
            );
        }

        return [
            'nav' => $this->frontendMenuArrayFormater($headerMenu),
            'navLogo' => $menuLogo,
            'dropdownRightMenus' => $dropdownRightMenus,
        ];
    }

    public function getFrontendUserFooterMenus(Request $request): array
    {
        $user = $request->user();

        $language = app(LanguageService::class)->getOriginLanguageFromCookie();

        if ($user) {
            $language =  $user->languageCode;
        }

        $footerMenu = $this->getFooterMenu($language);

        if ($footerMenu->isEmpty()) {
            $footerMenu = $this->getFooterMenu(
                app(TranslationService::class)->getDefaultLocale()
            );
        }

        return [
            'nav' => $this->frontendMenuArrayFormater($footerMenu),
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

    public function removePageFromMenus(int $pageId, ?string $locale = null)
    {
        $menuItems = $this->getQueryBuilderMenuItem($pageId, $locale)->get();

        $menuItems->each(function ($menuItem) {
            $menuItem->delete();
        });

        app(MenuCache::class)->flush();
    }

    public function isPageUsedByMenu(int $pageId, ?string $locale = null): bool
    {
        return $this->getQueryBuilderMenuItem($pageId, $locale)->exists();
    }

    private function getQueryBuilderMenuItem(int $pageId, ?string $locale): Builder
    {
        return MenuItem::where('page_id', $pageId)
            ->whereHas('menu', function ($q) use ($locale) {
                $q->when($locale, function ($q) use ($locale) {
                    $q->where('locale', $locale);
                });
            });
    }

    private function moduleMenus(Request $request, $method = 'admin'): array
    {
        $modules = app(ModuleService::class)->getAllEnabledNames();

        $menus = [];

        foreach ($modules as $module) {
            $moduleService = '\\Modules\\'.$module.'\\ModuleService';

            $methodName = $method.'Menus';

            if (
                class_exists($moduleService)
                && method_exists($moduleService, $methodName)
            ) {
                $menus[] = $moduleService::$methodName($request);
            }
        }

        return $menus;
    }
}
