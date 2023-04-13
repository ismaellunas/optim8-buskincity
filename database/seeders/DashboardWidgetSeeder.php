<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DashboardWidgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $widgets = [
            [
                "uuid" => Str::uuid(),
                "widget" => "App\Entities\Widgets\TotalUsersWidget",
                "module" => null,
                "title" => "Performers",
                "background_color" => "#6ac158",
                "order" => 1,
                "grid" => 6,
                "setting" => [
                    'form_id' => 3,
                    'role_id' => 4,
                ],
            ],
            [
                "uuid" => Str::uuid(),
                "widget" => "Modules\Space\Widgets\TotalCitiesWidget",
                "module" => "Space",
                "title" => "Cities",
                "background_color" => "#58b6c2",
                "order" => 2,
                "grid" => 6,
                "setting" => [
                    'form_id' => 3,
                ],
            ],
            [
                "uuid" => Str::uuid(),
                "widget" => "Modules\Booking\Widgets\TotalBookingsWidget",
                "module" => "Booking",
                "background_color" => "#f88f02",
                "order" => 3,
                "grid" => 6,
                "setting" => [],
            ],
            [
                "uuid" => Str::uuid(),
                "widget" => "Modules\FormBuilder\Widgets\TotalEntriesWidget",
                "module" => "FormBuilder",
                "title" => "Contact Us",
                "background_color" => "#6ac158",
                "order" => 4,
                "grid" => 6,
                "setting" => [
                    "form_id" => 4,
                ],
            ]
        ];

        Setting::create([
            "key" => "dashboard_widget_admin",
            "display_name" => null,
            "group" => "widget",
            "value" => json_encode($widgets),
        ]);
    }
}
