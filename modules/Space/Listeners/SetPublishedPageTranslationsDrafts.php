<?php

namespace Modules\Space\Listeners;

use Modules\Space\Events\ModuleDeactivated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Space\Entities\PageTranslation;

class SetPublishedPageTranslationsDrafts implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ModuleDeactivated $event)
    {
        $spacePageTranslations = PageTranslation::published()
            ->get(['id', 'page_id', 'unique_key', 'status', 'locale', 'title']);

        foreach ($spacePageTranslations as $spacePageTranslation) {
            $spacePageTranslation->setAsDraft();
        }
    }
}
