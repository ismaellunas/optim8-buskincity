<?php

namespace Database\Seeders\Buskincity;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $widgetPerformerApplications = [
            [
                "key" => "page_id",
                "display_name" => null,
                "value" => null,
                "group" => "widget.performer_application",
                "order" => 1,
            ],
            [
                "key" => "form_id",
                "display_name" => null,
                "value" => null,
                "group" => "widget.performer_application",
                "order" => 2,
            ],
        ];

        foreach ($widgetPerformerApplications as $widgetPerformerApplication) {
            $this->createSetting($widgetPerformerApplication);
        }
    }

    private function createSetting($data)
    {
        Setting::factory()->create(array_merge($data, [
            "created_at" => now(),
            "updated_at" => now(),
        ]));
    }
}
