<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceService;

class FixSpaceMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:space-media';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix space media relation.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $spaces = Space::all();

        foreach ($spaces as $space) {
            $logoMediaId = $space->logo_media_id;
            if ($logoMediaId) {
                $space->detachMedia($logoMediaId);

                app(SpaceService::class)
                    ->replaceLogo($space, $logoMediaId);
            }

            $coverMediaId = $space->cover_media_id;
            if ($coverMediaId) {
                $space->detachMedia($coverMediaId);

                app(SpaceService::class)
                    ->replaceCover($space, $coverMediaId);
            }
        }

        return Command::SUCCESS;
    }
}
