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
        $streetPerformerPage = PageTranslation::firstWhere('slug', 'street-performers');
        $streetPerformerPageUrl = $streetPerformerPage
            ? route('frontend.pages.show', [
                'page_translation' => $streetPerformerPage->slug,
            ])
            : null;

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
                            'page_id' => PageTranslation::firstWhere('slug', 'performer-application')
                                ->page_id ?? null,
                            'form_id' => Form::firstWhere('key', 'performer_application')
                                ->id ?? null,
                        ],
                        'i18n' => [
                            'description' => 'Apply to become a performer on BuskinCity. It will allow you to have your page, receive donations, private bookings, and build a bigger audience.',
                            'under_review' => 'Our team is reviewing your application. Usually takes from 3-5 days to get approved. We will contact you by email with our decision. Have questions or comments?',
                            'button_apply' => 'Apply now',
                            'button_get_in_touch' => 'Get in touch',
                        ],
                        'is_enabled' => true,
                    ],
                    [
                        'id' => Str::uuid(),
                        'widget' => "App\Entities\Widgets\DefaultWidget",
                        'module' => null,
                        'title' => 'Street Performers You Might Like',
                        'order' => 5,
                        'grid' => [
                            'desktop' => 6,
                            'tablet' => 12,
                            'mobile' => 12,
                        ],
                        'setting' => [
                            'url' => $streetPerformerPageUrl,
                            'visibility' => [
                                'role' => [],
                            ]
                        ],
                        'i18n' => [
                            'description' => 'Find and follow your favorite street performers on BuskinCity.',
                            'button_url' => 'Find out',
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
