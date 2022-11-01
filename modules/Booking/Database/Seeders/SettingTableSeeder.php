<?php

namespace Modules\Booking\Database\Seeders;

use App\Models\Setting;
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
        ];

        foreach ($settings as $setting) {
            Setting::factory()->create(array_merge($setting, [
                "created_at" => now(),
                "updated_at" => now(),
            ]));
        }
    }
}
