<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\{
    Media,
    Page,
    PageTranslation,
};
use App\Services\{
    SettingService,
    TranslationService,
};
use Illuminate\Support\Collection;

class PageController extends Controller
{
    private $baseRouteName = 'frontend.pages';
    private $translationService;

    public function __construct(
        TranslationService $translationService
    ) {
        $this->translationService = $translationService;
    }

    private function goToPageWithDefaultLocaleOrFallback(
        Page $page,
        string $locale
    ) {
        $defaultLocale = $this->translationService->getDefaultLocale();

        if (
            $page->hasTranslation($defaultLocale)
        ) {
            $pageTranslation = $page->translate($defaultLocale);

            return view('page', [
                'currentLanguage' => $locale,
                'images' => $this->getPageImages($pageTranslation, $locale),
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

    public function homePage()
    {
        $settingService = app(SettingService::class);

        $locale = $this->translationService->currentLanguage();

        $homePage = $settingService->getHomePage();

        $page = Page::with([
            'translations' => function ($query) use($locale) {
                $query->where('locale', $locale)
                ->published();
            },
        ])
        ->where('id', $homePage)
        ->first();

        return  count($page->translations ?? []) != 0 ? $this->show($page->translations[0]) : view('home', ['title' => env('APP_NAME')]);
    }
}
