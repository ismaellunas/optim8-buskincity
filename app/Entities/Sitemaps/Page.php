<?php

namespace App\Entities\Sitemaps;

use App\Models\Page as PageModel;
use App\Models\PageTranslation;
use App\Services\SettingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException;
use Qirolab\Theme\Theme;

class Page extends BaseSitemap
{
    public function urls(): array|Collection
    {
        $urls = $this->getModel()->map(function ($page) {
                return $this->createUrlTag($page);
            });

        $urls->prepend($this->homePageSitemapUrlTag());

        return $urls;
    }

    public function optionalTags(): array
    {
        $lastmod = Carbon::today();
        $pageTranslations = $this->getModel()->sortByDesc('updated_at');

        if (!$pageTranslations->isEmpty()) {
            $lastmod = $pageTranslations->first()->updated_at;
        }

        return array_merge(
            parent::optionalTags(),
            [
                'lastmod' => $lastmod,
            ]
        );
    }

    private function getModel(): Collection
    {
        return PageTranslation::inLanguages([$this->locale])
            ->published()
            ->orderBy('slug')
            ->get([
                'slug',
                'updated_at',
            ]);
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
