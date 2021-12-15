<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\{
    Media,
    Page,
    PageTranslation,
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

    private function goToPageWithDefaultLocaleOr404(
        Page $page,
        string $locale
    ) {
        $menus = $this->menuService->getHeaderMenu($locale);
        $defaultLocale = $this->translationService->getDefaultLocale();

        if (
            $page->hasTranslation($defaultLocale)
            && $this->isPageExistInMenu($menus, $page)
        ) {
            $pageTranslation = $page->translate($defaultLocale);

            return view('page', [
                'currentLanguage' => $locale,
                'images' => $this->getPageImages($pageTranslation, $locale),
                'page' => $pageTranslation,
            ]);
        } else {
            return abort(404);
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
        $page = $pageTranslation->page;

        if (!$page->hasTranslation($locale)) {
            return $this->goToPageWithDefaultLocaleOr404(
                $page,
                $locale
            );
        } else {
            $newPageTranslation = $page->translate($locale);

            if ($newPageTranslation->slug !== $pageTranslation->slug) {
                return redirect()->route($this->baseRouteName.'.show', [
                    $newPageTranslation->slug
                ]);
            }

            return view('page', [
                'currentLanguage' => $locale,
                'images' => $this->getPageImages($newPageTranslation, $locale),
                'page' => $newPageTranslation,
            ]);
        }
    }
}
