<?php

namespace App\Services;

use App\Entities\Menus\Options\UrlOption;
use App\Models\{
    Category,
    ErrorLog,
    Media,
    Menu,
    Page,
    Post,
    Role,
    Setting,
    User,
};
use App\Services\{
    LanguageService,
    LoginService,
    ModuleService,
};
use Illuminate\Http\Request;
use App\Entities\Caches\{
    MenuCache,
    SettingCache,
};
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Illuminate\Database\Eloquent\Model;

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

    private function getTypeMenuClass(string $type): ?string
    {
        $className = "\\App\\Entities\\Menus\\".Str::studly($type)."Menu";

        if (class_exists($className)) {
            return $className;
        }

        return $this->getModuleTypeMenuClass($type);
    }

    private function getModuleTypeMenuClass(string $type): ?string
    {
        $className = '\\Modules\\'. Str::studly($type) .'\\Menus\\MenuUrl';

        if (class_exists($className)) {
            return $className;
        }

        return null;
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

                if ($className) {
                    $menu = new $className($menuItem, $locale);

                    if ($menuItems->contains('parent_id', $menuItem->id)) {
                        $menu->children = $this->createStructuredMenus(
                            $menuItems,
                            $locale,
                            $menuItem->id
                        );
                    }

                    $menus->push($menu);
                }
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

    private function getThemeHeaderMenu(string $locale): array
    {
        return app(MenuCache::class)->rememberForLocale(
            'header_menu',
            function () use ($locale) {
                $menus = $this->getStructuredHeaderMenu($locale);

                if ($menus->isEmpty()) {
                    $menus = $this->getStructuredHeaderMenu(defaultLocale());
                }

                return $this->frontendMenuArrayFormater($menus);
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

    private function getThemeFooterMenu(string $locale): array
    {
        return app(MenuCache::class)->rememberForLocale(
            'footer_menu',
            function () use ($locale) {
                $menus = $this->getStructuredFooterMenu($locale);

                if ($menus->isEmpty()) {
                    $menus = $this->getStructuredFooterMenu(defaultLocale());
                }

                return $this->frontendMenuArrayFormater($menus);
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
                            $type = (new UrlOption)->getKey();

                            $query->select([
                                    'id',
                                    'url',
                                    'icon',
                                    'is_blank',
                                    'menu_id',
                                ]);
                            $query->where('type', $type);
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
                'title' => __('Dashboard'),
                'link' => route('admin.dashboard'),
            ];

            $menus = [
                [
                    'title' => __('Dashboard'),
                    'link' => route('admin.dashboard'),
                    'isActive' => $request->routeIs('admin.dashboard'),
                    'isEnabled' => true,
                ],
                [
                    'title' => __('Pages'),
                    'link' => route('admin.pages.index'),
                    'isActive' => $request->routeIs('admin.pages.*'),
                    'isEnabled' => $user->can('viewAny', Page::class),
                ],
                [
                    'title' => __('Blog'),
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
                            'title' => __('Posts'),
                            'link' => route('admin.posts.index'),
                            'isActive' => $request->routeIs('admin.posts.*'),
                            'isEnabled' => $user->can('viewAny', Post::class),
                        ],
                        [
                            'title' => __('Categories'),
                            'link' => route('admin.categories.index'),
                            'isActive' => $request->routeIs('admin.categories.*'),
                            'isEnabled' => $user->can('viewAny', Category::class),
                        ],
                    ],
                ],
                [
                    'title' => __('Media'),
                    'link' => route('admin.media.index'),
                    'isActive' => $request->routeIs('admin.media.*'),
                    'isEnabled' => $user->can('viewAny', Media::class),
                ],
                [
                    'title' => __('Theme'),
                    'isActive' => $request->routeIs('admin.theme.*'),
                    'isEnabled' => $user->can('system.theme'),
                    'children' => [
                        [
                            'title' => __('Header'),
                            'link' => route('admin.theme.header.edit'),
                            'isActive' => $request->routeIs('admin.theme.header.*'),
                            'isEnabled' => true,
                        ],
                        [
                            'title' => __('Footer'),
                            'link' => route('admin.theme.footer.edit'),
                            'isActive' => $request->routeIs('admin.theme.footer.*'),
                            'isEnabled' => true,
                        ],
                        [
                            'title' => __('Colors'),
                            'link' => route('admin.theme.color.edit'),
                            'isActive' => $request->routeIs('admin.theme.color.*'),
                            'isEnabled' => true,
                        ],
                        [
                            'title' => __('Fonts'),
                            'link' => route('admin.theme.fonts.edit'),
                            'isActive' => $request->routeIs('admin.theme.fonts.*'),
                            'isEnabled' => true,
                        ],
                        [
                            'title' => __('Advanced'),
                            'link' => route('admin.theme.advance.edit'),
                            'isActive' => $request->routeIs('admin.theme.advance.*'),
                            'isEnabled' => true,
                        ],
                        [
                            'title' => Str::upper(__('Seo')),
                            'link' => route('admin.theme.seo.edit'),
                            'isActive' => $request->routeIs('admin.theme.seo.*'),
                            'isEnabled' => true,
                        ],
                    ],
                ],
                [
                    'title' => __('Settings'),
                    'isActive' => $request->routeIs('admin.setting.*'),
                    'isEnabled' => (
                        $user->can('system.language')
                        || $user->can('system.translation')
                        || $user->can('system.payment')
                        || $user->can('system.log')
                        || $user->can('manageKeys', Setting::class)
                    ),
                    'children' => [
                        [
                            'title' => __('Languages'),
                            'link' => route('admin.settings.languages.edit'),
                            'isActive' => $request->routeIs('admin.settings.languages.edit'),
                            'isEnabled' => $user->can('system.language'),
                        ],
                        [
                            'title' => Str::title(__('Translation manager')),
                            'link' => route('admin.settings.translation-manager.edit'),
                            'isActive' => $request->routeIs('admin.settings.translation-manager.edit'),
                            'isEnabled' => $user->can('system.translation'),
                        ],
                        [
                            'title' => __('Stripe'),
                            'link' => route('admin.settings.stripe.edit'),
                            'isActive' => $request->routeIs('admin.settings.stripe.edit'),
                            'isEnabled' => Gate::check('manageStripeSetting', $user),
                        ],
                        [
                            'title' => __('Keys'),
                            'link' => route('admin.settings.keys.edit'),
                            'isActive' => $request->routeIs('admin.settings.keys.edit'),
                            'isEnabled' => $user->can('manageKeys', Setting::class),
                        ],
                        [
                            'title' => Str::title(__('System log')),
                            'link' => route('admin.system-log.index'),
                            'isActive' => $request->routeIs('admin.system-log.*'),
                            'isEnabled' => $user->can('system.log'),
                        ],
                        [
                            'title' => Str::title(__('Error log')),
                            'link' => route('admin.error-log.index'),
                            'isActive' => $request->routeIs('admin.error-log.*'),
                            'isEnabled' => $user->can('viewAny', ErrorLog::class),
                        ],
                        [
                            'title' => __('GDPR Cookie Consent'),
                            'link' => route('admin.settings.cookie-consent.edit'),
                            'isActive' => $request->routeIs('admin.settings.cookie-consent.*'),
                            'isEnabled' => $user->can('system.cookie_consent'),
                        ],
                        [
                            'title' => __('Module'),
                            'link' => route('admin.settings.modules.index'),
                            'isActive' => $request->routeIs('admin.settings.modules.*'),
                            'isEnabled' => $user->isSuperAdministrator,
                        ],
                    ],
                ],
                [
                    'title' => __('Users'),
                    'isActive' => $request->routeIs('admin.users.*') || $request->routeIs('admin.roles.*'),
                    'isEnabled' => $user->can('viewAny', User::class) || $user->can('viewAny', Role::class),
                    'children' => [
                        [
                            'title' => Str::title(__('All users')),
                            'link' => route('admin.users.index'),
                            'isActive' => $request->routeIs('admin.users.*'),
                            'isEnabled' => $user->can('viewAny', User::class),
                        ],
                        [
                            'title' => __('Roles'),
                            'link' => route('admin.roles.index'),
                            'isActive' => $request->routeIs('admin.roles.*'),
                            'isEnabled' => $user->can('viewAny', Role::class),
                        ],
                    ],
                ],
            ];

            $moduleMenus = $this->moduleMenus($request);

            $menuProfile = [
                'title' => __('Profile'),
                'link' => route('admin.profile.show'),
            ];

        } else {

            $menus = [
                [
                    'title' => __('Dashboard'),
                    'link' => route('dashboard'),
                    'isActive' => $request->routeIs('dashboard'),
                    'isEnabled' => true,
                ],
            ];

            $moduleMenus = [];

            $menuProfile = [
                'title' => __('Profile'),
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
        $language = app(LanguageService::class)->getOriginLanguageFromCookie(
            currentLocale()
        );

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
            } else if ($user->hasVerifiedEmail()) {
                $dropdownRightMenus = [
                    [
                        'title' => 'Dashboard',
                        'link' => route('dashboard'),
                        'isEnabled' => true,
                    ],
                    [
                        'title' => 'Payments',
                        'link' => route('payments.index'),
                        'isEnabled' => Gate::check('manageStripeConnectedAccount', $user),
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

        return [
            'nav' => $this->getThemeHeaderMenu($language),
            'navLogo' => $menuLogo,
            'dropdownRightMenus' => $dropdownRightMenus,
        ];
    }

    public function getFrontendUserFooterMenus(Request $request): array
    {
        $user = $request->user();

        $language = app(LanguageService::class)->getOriginLanguageFromCookie(
            currentLocale()
        );

        if ($user) {
            $language =  $user->languageCode;
        }

        return [
            'nav' => $this->getThemeFooterMenu($language),
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

    private function menuBuilderClasses(): array
    {
        return array_merge([
            \App\Entities\Menus\Options\UrlOption::class,
            \App\Entities\Menus\Options\PageOption::class,
            \App\Entities\Menus\Options\PostOption::class,
            \App\Entities\Menus\Options\CategoryOption::class,
            \App\Entities\Menus\Options\SegmentOption::class,
        ], $this->moduleMenuBuilderClasses());
    }

    private function moduleMenuBuilderClasses(): array
    {
        $classes = [];

        foreach (app(ModuleService::class)->modules() as $activeModule) {
            $classes[] = '\\Modules\\'. $activeModule->name .'\\Menus\\MenuOption';
        }

        return $classes;
    }

    public function getMenuItemTypeOptions(bool $isDisplayAllOption = false): array
    {
        $options = [];

        foreach ($this->menuBuilderClasses() as $menuBuilderClass) {
            if (class_exists($menuBuilderClass)) {
                $menuBuilder = new $menuBuilderClass;

                if (!$isDisplayAllOption) {
                    if ($menuBuilder->isOptionDisplayed()) {
                        $options[] = $menuBuilder->getTypeOptions();
                    }
                } else {
                    $options[] = $menuBuilder->getTypeOptions();
                }
            }
        }

        return collect($options)
            ->sortBy('value')
            ->values()
            ->all();
    }

    public function getMenuOptions(): array
    {
        $options = [];

        foreach ($this->menuBuilderClasses() as $menuBuilderClass) {
            if (class_exists($menuBuilderClass)) {
                $menuBuilder = new $menuBuilderClass;

                $options[$menuBuilder->getKey()] = $menuBuilder->getMenuOptions();
            }
        }

        return $options;
    }

    public function removeModelFromMenus(Model $model, ?string $locale = null)
    {
        $menuItems = $this->getCollectionMenuItem($model, $locale);

        $menuItems->each(function ($menuItem) {
            $menuItem->delete();
        });

        app(MenuCache::class)->flush();
    }

    public function isModelUsedByMenu(Model $model, ?string $locale = null): bool
    {
        return $this->getCollectionMenuItem($model, $locale)->isNotEmpty();
    }

    private function getCollectionMenuItem(Model $model, ?string $locale): Collection
    {
        return $model->menuItems->when($locale, function ($collection) use ($locale) {
            return $collection->filter(function ($menuItem) use ($locale) {
                return $menuItem->menu->locale == $locale;
            });
        });
    }

    private function moduleMenus(Request $request, $method = 'admin'): array
    {
        $methodName = $method.'Menus';

        $menus = [];

        $appModuleService = app(ModuleService::class);

        $orderedModules = $appModuleService->modules()->sortBy('order');

        foreach ($orderedModules as $module) {

            $moduleService = $appModuleService->getServiceClass($module);

            if (method_exists($moduleService, $methodName)) {
                $menus[] = $moduleService->$methodName($request);
            }
        }

        return $menus;
    }
}
