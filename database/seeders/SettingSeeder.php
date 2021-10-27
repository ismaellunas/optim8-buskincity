<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
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
                "key" => "color_primary",
                "display_name" => "Primary Color",
                "value" => "#00d1b2",
                "group" => "theme_color",
                "order" => "1"
            ],
            [
                "key" => "color_link",
                "display_name" => "Link Color",
                "value" => "#485fc7",
                "group" => "theme_color",
                "order" => "2"
            ],
            [
                "key" => "color_info",
                "display_name" => "Info Color",
                "value" => "#3e8ed0",
                "group" => "theme_color",
                "order" => "3"
            ],
            [
                "key" => "color_success",
                "display_name" => "Success Color",
                "value" => "#48c78e",
                "group" => "theme_color",
                "order" => "4"
            ],
            [
                "key" => "color_warning",
                "display_name" => "Warning Color",
                "value" => "#ffe08a",
                "group" => "theme_color",
                "order" => "5"
            ],
            [
                "key" => "color_danger",
                "display_name" => "Danger Color",
                "value" => "#f14668",
                "group" => "theme_color",
                "order" => "6"
            ]
        ];

        foreach ($settings as $setting) {
            Setting::factory()->create($setting);
        }
    }
}
