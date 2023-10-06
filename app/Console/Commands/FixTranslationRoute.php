<?php

namespace App\Console\Commands;

use App\Entities\Caches\TranslationCache;
use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Str;


class FixTranslationRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:translation-route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update translation route for public profile url';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $routes = Translation::where('group', 'routes')
            ->where('key', 'frontend.profile')
            ->get();

        foreach ($routes as $route) {
            $route->value = Str::replace("firstname_lastname", "slug", $route->value);
            $route->save();
        }

        app(TranslationCache::class)->flush();

        return Command::SUCCESS;
    }
}
