<?php

namespace Modules\Space\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Space\Entities\Space;
use Modules\Space\Events\ModuleDeactivated;

class UnassignAllSpaceManagers implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ModuleDeactivated $event)
    {
        $managedSpaces = Space::whereHas('managers')
            ->get(['id', '_lft', '_rgt', 'parent_id']);

        foreach ($managedSpaces as $managedSpace) {
            $managedSpace->managers()->detach();
        }
    }
}
