<?php

namespace App\Entities\Sitemaps;

use App\Models\Page as PageModel;
use App\Models\PageTranslation;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException;
use Qirolab\Theme\Theme;

class Page extends BaseSitemap
{
    public function urls(): array|Collection
    {
        $urls = $this->getEloquentBuilder()
            ->orderBy('slug')
            ->get()
            ->map(function ($pageTranslation) {
                if (!$pageTranslation->page->isHomePage) {
                    return $this->createUrlTag($pageTranslation);
                }
            })
            ->filter();

        $urls->prepend($this->homePageSitemapUrlTag());

        return $urls;
    }

    public function optionalTags(): array
    {
        $lastmod = Carbon::today();
        $latestPageTranslation = $this->getEloquentBuilder()
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($latestPageTranslation) {
            $lastmod = $latestPageTranslation->updated_at;
        }

        return array_merge(
            parent::optionalTags(),
            [
                'lastmod' => $lastmod,
            ]
        );
    }

    private function getEloquentBuilder(): Builder
    {
        return PageTranslation::select([
                'slug',
                'updated_at',
                'page_id',
            ])
            ->with(['page'])
            ->inLanguages([$this->locale])
            ->published();
    }

    private function createUrlTag(PageTranslation $pageTranslation): UrlTag
    {
        return new UrlTag(
            $this->locationUrl(route('frontend.pages.show', [$pageTranslation->slug])),
            ['lastmod' => $pageTranslation->updated_at]
        );
    }

    private function homePageModel(): ?PageModel
    {
        $homePageId = app(SettingService::class)->getHomePage();

        if (!$homePageId) {
            return null;
        }

        return PageModel::
            with([
                'translations' => function ($query) {
                    $query
                        ->published()
                        ->select([
                            'id',
                            'page_id',
                            'locale',
                            'slug',
                            'updated_at',
                        ]);
                },
            ])
            ->find($homePageId);
    }

    private function getLastModifiedThemeHomeBlade(): Carbon
    {
        $homeBladePath = 'views/home.blade.php';

        try {
            $timestamp = Storage::disk('themes')->lastModified(Theme::active().'/'.$homeBladePath);
        } catch (FileNotFoundException $e) {
            $timestamp = Storage::disk('themes')->lastModified(Theme::parent().'/'.$homeBladePath);
        }

        return new Carbon($timestamp);
    }

    private function homePageSitemapUrlTag(): UrlTag
    {
        $page = $this->homePageModel();

        if ($page) {
            $lastmod = $page->hasTranslation($this->locale)
                ? $page->translate($this->locale)->updated_at
                : $page->translations->first()->updated_at;
        } else {
            $lastmod = $this->getLastModifiedThemeHomeBlade();
        }

        return new UrlTag(
            route('homepage').'/',
            ['lastmod' => $lastmod]
        );
    }
}
