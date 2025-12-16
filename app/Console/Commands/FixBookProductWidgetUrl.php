<?php

namespace App\Console\Commands;

use App\Entities\Caches\SettingCache;
use App\Services\SettingService;
use Illuminate\Console\Command;

class FixBookProductWidgetUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:book-product-widget-url';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the BookProductWidget URL to use the correct booking products path';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $widgets = collect(
            app(SettingService::class)->getArrayValueByKey('dashboard_widget')
        )
        ->transform(function ($widget) {
            if (
                $widget['widget'] == 'Modules\Booking\Widgets\BookProductWidget'
                && isset($widget['setting']['url'])
            ) {
                $this->info("Updating BookProductWidget URL from: {$widget['setting']['url']}");
                $widget['setting']['url'] = '/booking/products';
                $this->info("Updated to: {$widget['setting']['url']}");
            }

            return $widget;
        });

        app(SettingService::class)->saveKey(
            'dashboard_widget',
            $widgets->all()
        );

        app(SettingCache::class)->flush();

        $this->info('BookProductWidget URL has been fixed successfully!');

        return Command::SUCCESS;
    }
}

