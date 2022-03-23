<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class StripeSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                "key" => "stripe_color_primary",
                "display_name" => "Primary Color",
                "value" => "#395dbf",
                "group" => "stripe",
            ],
            [
                "key" => "stripe_color_secondary",
                "display_name" => "Secondary Color",
                "value" => "#fcd42f",
                "group" => "stripe",
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                [
                    'key' => $setting['key'],
                ],
                [
                    "display_name" => $setting['display_name'],
                    "value" => $setting['value'],
                    "group" => $setting['group'],
                ]
            );
        }
    }
}
