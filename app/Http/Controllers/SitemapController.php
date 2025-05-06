<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use App\Services\TranslationService;

class SitemapController extends Controller
{
    private $locale;
    private $sitemapService;

    public function __construct(
        TranslationService $translationService,
        SitemapService $sitemapService
    ) {
        $this->locale = $translationService->currentLanguage();
        $this->sitemapService = $sitemapService;
    }

    private function responseXML(string $view, array $data = [])
    {
        return response()
            ->view('sitemap.'.$view, $data)
            ->header('Content-Type', 'text/xml');
    }

    public function urls(string $sitemapName)
    {
        $sitemap = $this->sitemapService->sitemap($sitemapName, $this->locale);

        return $this->responseXML('urls', [
            'urls' => $sitemap->urls($this->locale)
        ]);
    }

    public function sitemaps()
    {
        return $this->responseXML('sitemaps', [
            'sitemaps' => $this->sitemapService->sitemaps($this->locale)
        ]);
    }
}
