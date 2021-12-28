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

    private function goToPageWithDefaultLocaleOrFallback(
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
                'images' => $this->getPageImages($pageTranslation),
                'page' => $pageTranslation,
            ]);
        } else {
            return $this->redirectFallback();
        }
    }

    private function redirectFallback()
    {
        return redirect()->route('homepage');
    }

    private function getPageImages(
        PageTranslation $pageTranslation,
    ): Collection {
        $images = collect([]);

        if (!empty($pageTranslation->data['media'])) {
            $mediaIds = collect($pageTranslation->data['media'])->pluck('id');

            $images = Media::whereIn('id', $mediaIds)
                ->image()
                ->with([
                    'translations' => function ($q) {
                        $q->select(['id', 'locale', 'alt', 'media_id']);
                    },
                ])
                ->get(['id', 'file_url'])
                ->transform(function ($media) {
                    $media->alt = $media->alt ?? $media->translations[0]->alt;

                    return $media;
                });
        }

        return $images;
    }

    public function show(
        PageTranslation $pageTranslation
    ) {
        $locale = $this->translationService->currentLanguage();
        $page = $pageTranslation->page;

        if (!$page->hasTranslation($locale)) {
            return $this->goToPageWithDefaultLocaleOrFallback(
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
                'images' => $this->getPageImages($newPageTranslation),
                'page' => $newPageTranslation,
            ]);
        }
    }
}
