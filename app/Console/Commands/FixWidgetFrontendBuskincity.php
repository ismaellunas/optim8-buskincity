<?php

namespace App\Console\Commands;

use App\Entities\Caches\SettingCache;
use App\Models\PageTranslation;
use App\Services\SettingService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixWidgetFrontendBuskincity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:widget-frontend-buskincity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add Street Performers You Might Like widget on Buskincity';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $widgetLists = collect(
            app(SettingService::class)->getArrayValueByKey('dashboard_widget_buskincity')
        );

        $streetPerformerPage = PageTranslation::firstWhere('slug', 'street-performers');
        $streetPerformerPageUrl = $streetPerformerPage
            ? route('frontend.pages.show', [
                'page_translation' => $streetPerformerPage->slug,
            ])
            : null;

        $widgetLists = $widgetLists->push([
            'id' => Str::uuid(),
            'widget' => "App\Entities\Widgets\DefaultWidget",
            'module' => null,
            'title' => 'Street Performers You Might Like',
            'order' => 5,
            'grid' => [
                'desktop' => 6,
                'tablet' => 12,
                'mobile' => 12,
            ],
            'setting' => [
                'url' => $streetPerformerPageUrl,
                'visibility' => [
                    'role' => [],
                ]
            ],
            'i18n' => [
                'description' => 'Find and follow your favorite street performers on BuskinCity.',
                'button_url' => 'Find out',
            ],
            'is_enabled' => true,
        ]);

        app(SettingService::class)->saveKey(
            'dashboard_widget_buskincity',
            $widgetLists->all()
        );

        app(SettingCache::class)->flush();

        return Command::SUCCESS;
    }
}
