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
                "value" => "#fc8e00",
                "group" => "theme_color",
                "order" => "1"
            ],
            [
                "key" => "color_link",
                "display_name" => "Link Color",
                "value" => "#0071b0",
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
            $this->createSetting($setting);
        }

        $headers = [
            [
                "key" => "header_layout",
                "display_name" => null,
                "value" => 1,
                "group" => "header",
                "order" => "1"
            ],
            [
                "key" => "header_logo_media_id",
                "display_name" => null,
                "value" => null,
                "group" => "header",
                "order" => "2"
            ],
        ];

        foreach ($headers as $header) {
            $this->createSetting($header);
        }

        $footers = [
            [
                "key" => "footer_layout",
                "display_name" => null,
                "value" => 1,
                "group" => "footer",
                "order" => "1"
            ],
        ];

        foreach ($footers as $footer) {
            $this->createSetting($footer);
        };

        $trackingCodes = [
            [
                "key" => "tracking_code_inside_head",
                "display_name" => "Tracking Codes: Inside <head>",
                "value" => null,
                "group" => "tracking_code",
                "order" => "1"
            ],
            [
                "key" => "tracking_code_after_body",
                "display_name" => "Tracking Codes: After <body>",
                "value" => null,
                "group" => "tracking_code",
                "order" => "2"
            ],
            [
                "key" => "tracking_code_before_body",
                "display_name" => "Tracking Codes: Before </body>",
                "value" => null,
                "group" => "tracking_code",
                "order" => "3"
            ],
        ];

        foreach ($trackingCodes as $trackingCode) {
            $this->createSetting($trackingCode);
        }

        $additionalCodes = [
            [
                "key" => "additional_css",
                "display_name" => "Additional CSS",
                "value" => null,
                "group" => "additional_code",
                "order" => "1"
            ],
            [
                "key" => "additional_javascript",
                "display_name" => "Additional Javascript",
                "value" => null,
                "group" => "additional_code",
                "order" => "2"
            ]
        ];

        foreach ($additionalCodes as $additionalCode) {
            $this->createSetting($additionalCode);
        }

        $homePage = [
            "key" => "home_page",
            "display_name" => null,
            "value" => null,
            "group" => "home_page",
            "order" => "1"
        ];

        $this->createSetting($homePage);

        $qrCodes = [
            [
                "key" => "qrcode_public_page_is_displayed",
                "display_name" => null,
                "value" => true,
                "group" => "qrcode_public_page",
                "order" => "1",
            ],
            [
                "key" => "qrcode_public_page_logo_media_id",
                "display_name" => null,
                "value" => null,
                "group" => "qrcode_public_page",
                "order" => "2",
            ],
        ];

        foreach ($qrCodes as $qrCode) {
            $this->createSetting($qrCode);
        }

        $others = [
            [
                "key" => "default_language",
                "display_name" => null,
                "value" => 30,
                "group" => null,
                "order" => "1",
            ],
            [
                "key" => "default_country",
                "display_name" => null,
                "value" => "US",
                "group" => null,
                "order" => "2",
            ],
        ];

        foreach ($others as $other) {
            $this->createSetting($other);
        }

        $this->populateFontSizeSetting();

        $this->populateSocialiteSetting();
    }

    private function createSetting($data)
    {
        Setting::factory()->create(array_merge($data, [
            "created_at" => now(),
            "updated_at" => now(),
        ]));
    }

    private function populateFontSizeSetting()
    {
        $defaultFontSizes = config('constants.theme_font_sizes');

        $fontSizes = [
            [
                "key" => "font_size_heading_1",
                "display_name" => "Heading 1",
                "value" => $defaultFontSizes['font_size_heading_1'],
                "group" => "font_size",
                "order" => "1"
            ],
            [
                "key" => "font_size_heading_2",
                "display_name" => "Heading 2",
                "value" => $defaultFontSizes['font_size_heading_2'],
                "group" => "font_size",
                "order" => "2"
            ],
            [
                "key" => "font_size_heading_3",
                "display_name" => "Heading 3",
                "value" => $defaultFontSizes['font_size_heading_3'],
                "group" => "font_size",
                "order" => "3"
            ],
            [
                "key" => "font_size_heading_4",
                "display_name" => "Heading 4",
                "value" => $defaultFontSizes['font_size_heading_4'],
                "group" => "font_size",
                "order" => "4"
            ],
            [
                "key" => "font_size_heading_5",
                "display_name" => "Heading 5",
                "value" => $defaultFontSizes['font_size_heading_5'],
                "group" => "font_size",
                "order" => "5"
            ],
            [
                "key" => "font_size_heading_6",
                "display_name" => "Heading 6",
                "value" => $defaultFontSizes['font_size_heading_6'],
                "group" => "font_size",
                "order" => "6"
            ],
            [
                "key" => "font_size_text",
                "display_name" => "Text",
                "value" => $defaultFontSizes['font_size_text'],
                "group" => "font_size",
                "order" => "7"
            ],
            [
                "key" => "font_size_small",
                "display_name" => "Small",
                "value" => $defaultFontSizes['font_size_small'],
                "group" => "font_size",
                "order" => "8"
            ]
        ];

        foreach ($fontSizes as $fontSize) {
            $this->createSetting($fontSize);
        }
    }

    private function populateSocialiteSetting()
    {
        $settings = [
            [
                "key" => "socialite_drivers",
                "display_name" => "Socialite Drivers",
                "value" => json_encode([
                    'google',
                    'facebook',
                ]),
                "group" => "socialite",
                "order" => "1",
            ],
        ];

        foreach ($settings as $setting) {
            $this->createSetting($setting);
        }
    }
}
