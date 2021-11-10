<?php

namespace App\Services;

use App\Models\{
    Category,
    Media,
    MenuItem,
    Page,
    Post,
    Role,
    User,
};
use App\Services\TranslationService as TranslationSv;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MenuService
{
    public function getMenuItemLastSaved(string $type)
    {
        $menu = MenuItem::orderBy('updated_at', 'DESC')
            ->whereHas('menu', function ($query) use ($type) {
                if ($type == "header") {
                    $query->header();
                } else {
                    $query->footer();
                }
            })
            ->first();

        if ($menu) {
            return Carbon::parse($menu->updated_at)->format('M d, Y \a\t h:i');
        }

        return '-';
    }

    private function getMenuItems(
        string $locale,
        string $type
    ): array {
        return MenuItem::
            whereHas('menu', function ($query) use ($type, $locale) {

                $query->where('locale', $locale);

                if ($type == "header") {
                    $query->header();
                } else {
                    $query->footer();
                }
            })
            ->orderBy('order', 'ASC')
            ->orderBy('parent_id', 'ASC')
            ->get()
            ->toArray();
    }

    private function generateMenuItems(
        array $menuItems,
        $parentId = null
    ): array {
        $menus = [];
        foreach ($menuItems as $menuItem) {
            if ($menuItem['parent_id'] == $parentId) {
                $children = $this->generateMenuItems($menuItems, $menuItem['id']);

                if ($children) {
                    $menuItem['children'] = $children;
                } else {
                    $menuItem['children'] = [];
                }

                $className = "\App\Entities\Menus\\".MenuItem::TYPE_VALUES[$menuItem['type']]."Menu";
                $typeMenu = new $className($menuItem['id']);
                $menuItem['link'] = $typeMenu->getUrl();

                $menus[] = $menuItem;
            }
        }

        return $menus;
    }

    public function generateMenus(
        ?string $locale = null,
        string $type = "header"
    ) :array {
        $menus = [];

        if ($locale === null) {
            $locales = TranslationSv::getLocaleOptions();
        } else {
            $locales = [
                ["id" => $locale]
            ];
        }

        foreach ($locales as $locale) {
            $menuItems = $this->getMenuItems($locale['id'], $type);
            $menus[$locale['id']] = $this->generateMenuItems($menuItems);
        }

        return $menus;
    }

    public static function generateBackendMenu(Request $request): array
    {
        $user = $request->user();

        $menus = [
            'dashboard' => [
                'title' => 'Dashboard',
                'link' => route('dashboard'),
                'isActive' => $request->routeIs('dashboard'),
                'isEnabled' => true,
            ]
        ];

        if ($user->can('system.dashboard') && $request->routeIs('admin.*')) {

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
            ];

            $navProfile = [
                'title' => 'Profile',
                'link' => route('admin.profile.show'),
            ];
        } else {
            $navProfile = [
                'title' => 'Profile',
                'link' => route('user.profile.show'),
            ];
        }

        return [
            'nav' => $menus,
            'navProfile' => $navProfile,
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
                    ]);
                },
            ])
            ->get(['id'])
            ->map(function ($page) {
                return [
                    'id' => $page->id,
                    'value' => $page->title,
                ];
            })
            ->all();
    }

    public function getPostOptions(): array
    {
        return Post::published()->get([
            'id',
            'locale',
            'status',
            'title',
        ])->map(function ($post) {
            return [
                'id' => $post->id,
                'value' => $post->title,
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
                return [
                    'id' => $category->id,
                    'value' => $category->name,
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
}
