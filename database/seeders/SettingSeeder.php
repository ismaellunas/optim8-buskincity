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

        $fontSizes = [
            [
                "key" => "font_size_heading_1",
                "display_name" => "Heading 1",
                "value" => 2,
                "group" => "font_size",
                "order" => "1"
            ],
            [
                "key" => "font_size_heading_2",
                "display_name" => "Heading 2",
                "value" => 1.75,
                "group" => "font_size",
                "order" => "2"
            ],
            [
                "key" => "font_size_heading_3",
                "display_name" => "Heading 3",
                "value" => 1.5,
                "group" => "font_size",
                "order" => "3"
            ],
            [
                "key" => "font_size_heading_4",
                "display_name" => "Heading 4",
                "value" => 1.25,
                "group" => "font_size",
                "order" => "4"
            ],
            [
                "key" => "font_size_heading_5",
                "display_name" => "Heading 5",
                "value" => 1.125,
                "group" => "font_size",
                "order" => "5"
            ],
            [
                "key" => "font_size_heading_6",
                "display_name" => "Heading 6",
                "value" => 1,
                "group" => "font_size",
                "order" => "6"
            ],
            [
                "key" => "font_size_text",
                "display_name" => "Text",
                "value" => null,
                "group" => "font_size",
                "order" => "7"
            ],
            [
                "key" => "font_size_small",
                "display_name" => "Small",
                "value" => null,
                "group" => "font_size",
                "order" => "8"
            ]
        ];

        foreach ($fontSizes as $fontSize) {
            Setting::factory()->create($fontSize);
        }
    }
}
