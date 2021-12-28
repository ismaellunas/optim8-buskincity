<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\{
    Media,
    Page,
    PageTranslation,
};
use App\Services\{
    PageService,
    TranslationService,
};
use Illuminate\Support\Collection;

class PageController extends Controller
{
    private $baseRouteName = 'frontend.pages';
    private $pageService;
    private $translationService;

    public function __construct(
        PageService $pageService,
        TranslationService $translationService
    ) {
        $this->pageService = $pageService;
        $this->translationService = $translationService;
    }

    private function goToPageWithDefaultLocaleOrFallback(
        Page $page,
        string $locale,
        $fallback
    ) {
        $defaultLocale = $this->translationService->getDefaultLocale();

        if ($page->hasTranslation($defaultLocale)) {
            $pageTranslation = $page->translate($defaultLocale);

            return view('page', [
                'currentLanguage' => $locale,
                'images' => $this->getPageImages($pageTranslation, $locale),
                'page' => $pageTranslation,
            ]);
        } else {
            return $fallback;
        }
    }

    private function redirectFallback()
    {
        return redirect()->route('homepage');
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
            return $this->goToPageWithDefaultLocaleOrFallback(
                $page,
                $locale,
                $this->redirectFallback()
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

    public function homePage()
    {
        $locale = $this->translationService->currentLanguage();

        $page = $this->pageService->getHomePage();

        if (!$page->hasTranslation($locale)) {
            return $this->goToPageWithDefaultLocaleOrFallback(
                $page,
                $locale,
                $this->defaultHomePage()
            );
        } else {
            $pageTranslation = $page->translate($locale);

            return view('page', [
                'currentLanguage' => $locale,
                'images' => $this->getPageImages($pageTranslation, $locale),
                'page' => $pageTranslation,
            ]);
        }
    }

    private function defaultHomePage()
    {
        return view('home', ['title' => env('APP_NAME')]);
    }
}
