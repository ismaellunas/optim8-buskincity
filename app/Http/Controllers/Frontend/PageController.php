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
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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

    private function redirectFallback()
    {
        return redirect()->route('homepage');
    }

    private function defaultHomePage()
    {
        return view('home', ['title' => env('APP_NAME')]);
    }

    private function userCanAccessPage(): bool
    {
        if (Auth::check()) {
            return Auth::user()->can('page.read');
        } else {
            return false;
        }
    }

    private function pageTranslationView(
        PageTranslation $pageTranslation,
        string $locale
    ): View {
        return view('page', [
            'currentLanguage' => $locale,
            'images' => $this->getPageImages($pageTranslation),
            'page' => $pageTranslation,
        ]);
    }

    private function goToPageWithDefaultLocaleOrFallback(
        Page $page,
        string $locale,
        $fallback
    ) {
        $defaultLocale = $this->translationService->getDefaultLocale();

        if ($page->hasTranslation($defaultLocale)) {
            return $this->pageTranslationView(
                $page->translate($defaultLocale),
                $locale
            );
        } else {
            return $fallback;
        }
    }

    private function getPageImages(
        PageTranslation $pageTranslation,
    ): Collection {
        $images = collect([]);

        if (!empty($pageTranslation->data['media'])) {
            $mediaIds = collect($pageTranslation->data['media'])->pluck('id');

            $images = Media::select([
                    'id',
                    'version',
                    'file_name',
                    'extension',
                ])
                ->selectDimension()
                ->whereIn('id', $mediaIds)
                ->image()
                ->with([
                    'translations' => function ($q) {
                        $q->select(['id', 'locale', 'alt', 'media_id']);
                    },
                ])
                ->get()
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
                $locale,
                $this->redirectFallback()
            );
        } else {
            if (
                !$page->translate($locale)->isPublished
                && !$this->userCanAccessPage()
            ) {
                if (
                    $page->hasTranslation(defaultLocale())
                    && $page->translate(defaultLocale())->isPublished
                ) {
                    return $this->goToPageWithDefaultLocaleOrFallback(
                        $page,
                        $locale,
                        $this->redirectFallback()
                    );
                }

                return $this->redirectFallback();
            }

            if ($page->translate($locale)->slug !== $pageTranslation->slug) {
                return redirect()->route($this->baseRouteName.'.show', [
                    $page->translate($locale)->slug
                ]);
            }

            if ($page->isHomePage) {
                return $this->redirectFallback();
            }

            return $this->pageTranslationView(
                $page->translate($locale),
                $locale
            );
        }
    }

    public function homePage()
    {
        $locale = $this->translationService->currentLanguage();

        $page = $this->pageService->getHomePage();

        if (!$page) {
            return $this->defaultHomePage();
        }

        if (!$page->hasTranslation($locale)) {
            return $this->goToPageWithDefaultLocaleOrFallback(
                $page,
                $locale,
                $this->defaultHomePage()
            );
        } else {

            return $this->pageTranslationView(
                $page->translate($locale),
                $locale
            );
        }
    }
}
