<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\{
    Media,
    PageTranslation
};
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class PageController extends Controller
{
    private $baseComponentName = 'Page/Frontend';
    private $baseRouteName = 'frontend.pages';

    private function redirectToPageLocaleOrDefaultLocale(
        PageTranslation $pageTranslation,
        string $locale
    ) {
        $page = $pageTranslation->page;

        if ($page->hasTranslation($locale)) {
            $pageTranslation = $page->translate($locale);

            return redirect()->route($this->baseRouteName.'.show', [
                'page_translation' => $pageTranslation->slug
            ]);
        } else {
            $defaultLocale = TranslationService::getDefaultLocale();
            $pageTranslation = $page->translate($defaultLocale);

            return Inertia::render($this->baseComponentName.'/Show', [
                'currentLanguage' => TranslationService::currentLanguage(),
                'images' => $this->getPageImages($pageTranslation, $defaultLocale),
                'page' => $pageTranslation,
            ]);
        }
    }

    private function getPageImages(
        PageTranslation $pageTranslation,
        string $locale
    ): Collection {
        $images = collect([]);

        $locales = array_unique([
            TranslationService::getDefaultLocale(),
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

    public function show(Request $request, PageTranslation $pageTranslation)
    {
        $locale = !$request->has('locale')
            ? TranslationService::currentLanguage()
            : $request->locale;
        if ($pageTranslation->locale != $locale) {

            return $this->redirectToPageLocaleOrDefaultLocale($pageTranslation, $locale);

        } else {

            return Inertia::render($this->baseComponentName.'/Show', [
                'currentLanguage' => TranslationService::currentLanguage(),
                'images' => $this->getPageImages($pageTranslation, $locale),
                'page' => $pageTranslation,
            ]);
        }
    }
}
