<?php

namespace Modules\Booking\Database\Seeders;

use App\Models\Setting;
use App\Services\UserService;
use Illuminate\Database\Seeder;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleIds = app(UserService::class)
            ->getRoleOptions()
            ->pluck('id')
            ->all();

        $settings = [
            [
                "key" => "allowed_early_check_in",
                "display_name" => "Allowed early check-in",
                "value" => 0,
                "group" => "booking",
                "order" => "1"
            ],
            [
                "key" => "check_in_radius",
                "display_name" => "Check-in radius",
                "value" => json_encode(['value' => null, 'unit' => 'm']),
                "group" => "booking",
                "order" => "2"
            ],
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
}
