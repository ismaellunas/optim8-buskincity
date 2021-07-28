<?php

namespace App\Services;

use App\Models\Page;

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
                    'link' => route('pages.show', [$locale, $translation->slug]),
                ];
            }
        }

        return $menus;
    }
}
