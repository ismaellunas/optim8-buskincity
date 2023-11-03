<?php

namespace Modules\Space\Listeners;

use Modules\Space\Events\ModuleDeactivated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceService;

class RemoveSpaceFromMenus implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ModuleDeactivated $event)
    {
        $spaces = Space::select([
                'id',
            ])
            ->has('menuItems')
            ->get()
            ->all();

        app(SpaceService::class)->removeAllMenus($spaces);
    }
}
