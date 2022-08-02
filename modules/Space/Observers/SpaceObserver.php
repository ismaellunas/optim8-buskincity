<?php

namespace Modules\Space\Observers;

use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceService;

class SpaceObserver
{
    public function deleting(Space $space): void
    {
        $allSpaces = array_merge(
            [$space],
            $space->descendants()->get()->all()
        );

        app(SpaceService::class)->removeAllMedia($allSpaces);
        app(SpaceService::class)->removeAllPages($allSpaces);
    }
}