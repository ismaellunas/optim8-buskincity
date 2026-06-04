<?php

namespace Modules\Space\Observers;

use App\Entities\Caches\MenuCache;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceService;

class SpaceObserver
{
    public function creating(Space $space)
    {
        $space->is_page_enabled = true;
    }

    public function created(Space $space)
    {
        $page = Page::createBasedOnSpace($space->name, auth()->id());

        $space->page_id = $page->id;
        $space->save();
    }

    public function saved(Space $space): void
    {
        app(MenuCache::class)->flush();
    }

    public function deleted(Space $space): void
    {
        app(MenuCache::class)->flush();
    }

    public function deleting(Space $space): void
    {
        $allSpaces = array_merge(
            [$space],
            $space->descendants->all()
        );

        app(SpaceService::class)->removeAllMedia($allSpaces);
        app(SpaceService::class)->removeAllPages($allSpaces);
        app(SpaceService::class)->removeAllMenus($allSpaces);
    }
}