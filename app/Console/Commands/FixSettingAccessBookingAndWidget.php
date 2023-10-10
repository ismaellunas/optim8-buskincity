<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\SettingService;
use App\Services\UserService;
use Illuminate\Console\Command;

class FixSettingAccessBookingAndWidget extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:setting-access-booking-and-widget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add setting for access booking frontend and update widget data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->provideAccessBookingSetting();
        $this->updateWidgetData();

        return Command::SUCCESS;
    }

    private function provideAccessBookingSetting(): void
    {
        $roleIds = collect(
                app(UserService::class)->getRoleOptions()
            )
            ->pluck('id')
            ->all();

        $settings = [
            [
                "key" => "booking_access_common_user",
                "display_name" => null,
                "value" => false,
                "group" => "booking",
                "order" => "3"
            ],
            [
                "key" => "booking_access_roles",
                "display_name" => null,
                "value" => json_encode($roleIds),
                "group" => "booking",
                "order" => "4"
            ],
        ];

        foreach ($settings as $setting) {
            Setting::factory()->create(array_merge($setting, [
                "created_at" => now(),
                "updated_at" => now(),
            ]));
        }
    }

    private function updateWidgetData(): void
    {
        $widgets = collect(
                app(SettingService::class)->getArrayValueByKey(
                    'dashboard_widget'
                )
            )
            ->transform(function ($widget) {
                if (
                    $widget['widget'] == 'App\Entities\Widgets\DefaultWidget'
                    && $widget['module'] == 'Booking'
                    && $widget['title'] == 'Book a product'
                ) {
                    $widget['widget'] = 'Modules\Booking\Widgets\BookProductWidget';
                }

                return $widget;
            });

        app(SettingService::class)->saveKey(
            'dashboard_widget',
            json_encode($widgets->all())
        );
    }
}
