<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class MenuService
{
    public static function generateMenus($locale)
    {
        $menus = [
            'navbar' => [],
        ];

        $rawMenus = [];

        $pages = Page::take(2)->get();
        foreach ($pages as $page) {
            $rawMenus[] = [
                'class' => \App\Models\Page::class,
                'id' => $page->id,
                'order' => $page->id,
            ];
        }

        /* TODO: remove this after menu feature is created
        $rawMenus = [
            [
                'class' => \App\Models\Page::class,
                'id' => 56,
                'order' => 0,
            ], ...
        ];
         */

        $sortedRawMenus = collect($rawMenus)->sortBy('order');

        foreach ($sortedRawMenus as $rawMenu) {
            $objMenu = $rawMenu['class']::find($rawMenu['id']);
            $translation = $objMenu->translateOrDefault($locale);
            if (!empty($translation)) {
                $menus['navbar'][] = [
                    'title' => $translation->title,
                    'link' => route('frontend.pages.show', [
                        'locale' => $locale,
                        'page_translation' => $translation->slug,
                    ]),
                ];
            }
        }

        $menus['navbar'][] = [
            'title' => 'Blog',
            'link' => route('blog.index', [$locale]),
        ];

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
}
