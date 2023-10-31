<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use App\Services\TranslationService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

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
        try {
            $sitemap = $this->sitemapService->sitemap($sitemapName, $this->locale);

            return $this->responseXML('urls', [
                'urls' => $sitemap->urls($this->locale)
            ]);

        } catch (FileNotFoundException $e) {
            return abort(404);
        }
    }

    public function sitemaps()
    {
        return $this->responseXML('sitemaps', [
            'sitemaps' => $this->sitemapService->sitemaps($this->locale)
        ]);
    }
}
