<?php

namespace Modules\Space\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Space\Entities\SpaceEvent;
use Modules\Space\Events\ModuleDeactivated;

class SetPublishedEventsDrafts implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ModuleDeactivated $event)
    {
        $publishedSpaceEvents = SpaceEvent::published()
            ->get(['id', 'status']);

        foreach ($publishedSpaceEvents as $publishedSpaceEvent) {
            $publishedSpaceEvent->setAsDraft();
        }
    }
}
