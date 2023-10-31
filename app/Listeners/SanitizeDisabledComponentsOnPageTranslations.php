<?php

namespace App\Listeners;

use App\Models\PageTranslation;
use App\Services\PageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SanitizeDisabledComponentsOnPageTranslations implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private PageService $pageService)
    {}

    public function handle()
    {
        $pageTranslations = PageTranslation::get(['id', 'locale', 'data']);

        foreach ($pageTranslations as $pageTranslation) {
            $componentIds = $this
                ->pageService
                ->sanitizeTranslationFromDisabledComponents($pageTranslation);

            if ($componentIds->isNotEmpty()) {
                $pageTranslation->saveQuietly();
            }
        }
    }
}
