<?php

namespace App\Console\Commands;

use App\Entities\Caches\SettingCache;
use App\Services\SettingService;
use Illuminate\Console\Command;

class FixWidgetFrontend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:widget-frontend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove unused widget frontend on core';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $unusedWidgets = [
            'Upcoming Events',
            'Street Performers You Might Like',
            'Want to Become a Street Performer?',
        ];

        $widgetLists = collect(
                app(SettingService::class)->getFrontendWidgetLists()
            )
            ->filter(function ($widget) use ($unusedWidgets) {
                return ! in_array($widget['title'], $unusedWidgets);
            })
            ->values();

        app(SettingService::class)->saveKey(
            'dashboard_widget',
            $widgetLists->all()
        );

        app(SettingCache::class)->flush();

        return Command::SUCCESS;
    }
}
