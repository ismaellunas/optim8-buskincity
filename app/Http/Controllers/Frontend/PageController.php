<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\{
    Media,
    PageTranslation
};
use App\Services\MenuService;
use App\Services\TranslationService;
use Illuminate\Support\Collection;

class PageController extends Controller
{
    private $baseRouteName = 'frontend.pages';
    private $menuService;
    private $translationService;

    public function __construct(
        MenuService $menuService,
        TranslationService $translationService
    ) {
        $this->menuService = $menuService;
        $this->translationService = $translationService;
    }

    private function isPageExistInMenu(array $menus, $page): bool
    {
        return collect($menus)->contains(function ($menu) use ($page) {

            if ($menu->page_id && $menu->page_id == $page->id) {

                return true;

            } elseif (!empty($menu->children)) {

                collect($menu->children)->contains(function ($child) use ($page) {
                    return $child->page_id && $child->page_id == $page->id;
                });
            }

            return false;
        });
    }

    private function redirectToPageLocaleOrDefaultLocale(
        PageTranslation $pageTranslation,
        string $locale
    ) {
        $page = $pageTranslation->page;
        $menus = $this->menuService->getHeaderMenu($locale);

        if (
            $page->hasTranslation($locale)
            && $this->isPageExistInMenu($menus, $page)
        ) {
            $pageTranslation = $page->translate($locale);

            return redirect()->route($this->baseRouteName.'.show', [
                $pageTranslation->slug
            ]);

        } else {

            return redirect()->route('homepage');
        }
    }

    private function getPageImages(
        PageTranslation $pageTranslation,
        string $locale
    ): Collection {
        $images = collect([]);

        $locales = array_unique([
            $this->translationService->getDefaultLocale(),
            $locale,
        ]);

        if (!empty($pageTranslation->data['media'])) {
            $mediaIds = collect($pageTranslation->data['media'])->pluck('id');

            $images = Media::whereIn('id', $mediaIds)
                ->image()
                ->with([
                    'translations' => function ($q) use ($locales) {
                        $q->select(['id', 'locale', 'alt', 'media_id']);
                        $q->whereIn('locale', $locales);
                    },
                ])
                ->get(['id', 'file_url']);
        }

        return $images;
    }

    public function show(
        PageTranslation $pageTranslation
    ) {
        $locale = $this->translationService->currentLanguage();

        if ($pageTranslation->locale != $locale) {

            return $this->redirectToPageLocaleOrDefaultLocale(
                $pageTranslation,
                $locale
            );

        } else {

            return view('page', [
                'currentLanguage' => $locale,
                'images' => $this->getPageImages($pageTranslation, $locale),
                'page' => $pageTranslation,
            ]);
        }
    }
}
