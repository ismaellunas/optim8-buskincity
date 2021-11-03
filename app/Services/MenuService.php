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
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class MenuService
{
    public function getMenuItemLastSaved()
    {
        $menu = MenuItem::orderBy('updated_at', 'DESC')
            ->whereHas('menu', function ($query) {
                $query->where('type', Menu::TYPE_HEADER);
            })
            ->first();

        if ($menu) {
            return Carbon::parse($menu->updated_at)->format('M d, Y \a\t h:i');
        }

        return '-';
    }

    private function getMenuItems($menuId = null): array
    {
        return MenuItem::orderBy('order', 'ASC')
            ->orderBy('parent_id', 'ASC')
            ->whereHas('menu', function ($query) {
                $query->where('type', Menu::TYPE_HEADER);
            })
            ->get()
            ->toArray();
    }

    private function generateMenuItems($locale, $menuItems, $parentId = null): array
    {
        $menus = [];
        foreach ($menuItems as $menuItem) {
            if ($menuItem['parent_id'] == $parentId) {
                $children = $this->generateMenuItems($locale, $menuItems, $menuItem['id']);

                if ($children) {
                    $menuItem['children'] = $children;
                } else {
                    $menuItem['children'] = [];
                }

                $className = "\App\Menus\\".$menuItem['type']."Menu";
                $typeMenu = new $className($locale, $menuItem['id']);
                $menuItem['title'] = $typeMenu->getTitle();
                $menuItem['link'] = $typeMenu->getUrl();

                $menus[] = $menuItem;
            }
        }

        return $menus;
    }

    public function generateMenus($locale = "en")
    {
        $menuItems = $this->getMenuItems();
        return $this->generateMenuItems($locale, $menuItems);
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
                            'link' => route('admin.theme.header.index'),
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
                            'title' => 'Font Size',
                            'link' => route('admin.theme.font-size.edit'),
                            'isActive' => $request->routeIs('admin.theme.font-size.*'),
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

    public function getRecordPages()
    {
        $pages = Page::all();
        return $pages->sortBy('title');
    }

    public function getRecordPosts()
    {
        $posts = Post::published()->get();
        return $posts->sortBy('title');
    }

    public function getRecordCategories()
    {
        $categories = Category::all();
        return $categories->sortBy('name');
    }
}
