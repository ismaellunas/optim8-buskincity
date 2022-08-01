<?php

namespace Modules\Space\Observers;

use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceService;

class SpaceObserver
{
    public function deleting(Space $space): void
    {
        $allChildren = app(SpaceService::class)->getAllChildren(
            $space->children()->get()
        );

        $allSpaces = array_merge($allChildren, [$space]);

        app(SpaceService::class)->removeAllMedia($allSpaces);
        app(SpaceService::class)->removeAllPages($allSpaces);
    }
}