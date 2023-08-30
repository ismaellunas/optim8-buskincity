<?php

namespace Database\Seeders\Buskincity;

use App\Models\PageTranslation;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\FormBuilder\Entities\Form;

class WidgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pageId = PageTranslation::where('slug', 'performer-application')
            ->first()
            ->page_id ?? null;

        $formId = Form::where('key', 'performer_application')
            ->first()
            ->id ?? null;

        $settings = [
            [
                "key" => "dashboard_widget_buskincity",
                "display_name" => null,
                "value" => json_encode([
                    [
                        'id' => Str::uuid(),
                        'widget' => "App\Entities\Widgets\PerformerApplicationLinkWidget",
                        'module' => null,
                        'title' => 'Become a Performer',
                        'order' => 1,
                        'grid' => [
                            'desktop' => 6,
                            'tablet' => 12,
                            'mobile' => 12,
                        ],
                        'setting' => [
                            'page_id' => $pageId,
                            'form_id' => $formId,
                        ],
                        'i18n' => [
                            'description' => 'Apply to become a performer on BuskinCity. It will allow you to have your page, receive donations, private bookings, and build a bigger audience.',
                            'under_review' => 'Our team is reviewing your application. Usually takes from 3-5 days to get approved. We will contact you by email with our decision. Have questions or comments?',
                            'button_apply' => 'Apply now',
                            'button_get_in_touch' => 'Get in touch',
                        ],
                        'is_enabled' => true,
                    ],
                ]),
                "group" => "dashboard_widget",
                "order" => "1"
            ],
        ];

        foreach ($settings as $setting) {
            $this->createSetting($setting);
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
