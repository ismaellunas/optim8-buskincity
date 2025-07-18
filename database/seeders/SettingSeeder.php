<?php

namespace Database\Seeders;

use App\Models\Role;
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

        $favicon = [
            "key" => "favicon_media_id",
            "display_name" => null,
            "value" => null,
            "group" => "favicon",
            "order" => "1",
        ];

        $this->createSetting($favicon);

        $favicon = [
            "key" => "max_file_size",
            "display_name" => null,
            "value" => config('constants.max_file_size'),
            "group" => "validation.file",
            "order" => "1",
        ];

        $this->createSetting($favicon);

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

        $this->populateKeySetting();

        $this->populateRecaptchaScoreSetting();

        $this->populateCookieConsentSetting();

        $this->populatePublicPageProfileSetting();
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
                "value" => json_encode($defaultFontSizes['font_size_heading_1']),
                "group" => "font_size",
                "order" => "1"
            ],
            [
                "key" => "font_size_heading_2",
                "display_name" => "Heading 2",
                "value" => json_encode($defaultFontSizes['font_size_heading_2']),
                "group" => "font_size",
                "order" => "2"
            ],
            [
                "key" => "font_size_heading_3",
                "display_name" => "Heading 3",
                "value" => json_encode($defaultFontSizes['font_size_heading_3']),
                "group" => "font_size",
                "order" => "3"
            ],
            [
                "key" => "font_size_heading_4",
                "display_name" => "Heading 4",
                "value" => json_encode($defaultFontSizes['font_size_heading_4']),
                "group" => "font_size",
                "order" => "4"
            ],
            [
                "key" => "font_size_heading_5",
                "display_name" => "Heading 5",
                "value" => json_encode($defaultFontSizes['font_size_heading_5']),
                "group" => "font_size",
                "order" => "5"
            ],
            [
                "key" => "font_size_heading_6",
                "display_name" => "Heading 6",
                "value" => json_encode($defaultFontSizes['font_size_heading_6']),
                "group" => "font_size",
                "order" => "6"
            ],
            [
                "key" => "font_size_text",
                "display_name" => "Text",
                "value" => json_encode($defaultFontSizes['font_size_text']),
                "group" => "font_size",
                "order" => "7"
            ],
            [
                "key" => "font_size_small",
                "display_name" => "Small",
                "value" => json_encode($defaultFontSizes['font_size_small']),
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
                    'twitter',
                ]),
                "group" => "socialite",
                "order" => "1",
            ],
        ];

        foreach ($settings as $setting) {
            $this->createSetting($setting);
        }
    }

    private function populateKeySetting()
    {
        $settings = config('constants.settings.keys');

        foreach ($settings as $setting) {
            $setting['value'] = env(Str::upper($setting['key']), null);

            $this->createSetting($setting);
        }
    }

    private function populateRecaptchaScoreSetting(): void
    {
        $settings = [
            [
                "key" => "recaptcha_score",
                "display_name" => null,
                "value" => 0.6,
                "group" => "recaptcha.score",
                "order" => 1,
            ],
            [
                "key" => "recaptcha_score_register",
                "display_name" => null,
                "value" => 0.7,
                "group" => "recaptcha.score",
                "order" => 2,
            ],
            [
                "key" => "recaptcha_score_forgot_password",
                "display_name" => null,
                "value" => 0.7,
                "group" => "recaptcha.score",
                "order" => 3,
            ],
        ];

        foreach ($settings as $setting) {
            $this->createSetting($setting);
        }
    }

    private function populateCookieConsentSetting()
    {
        $settings = [
            [
                "key" => "cookie_consent_is_enabled",
                "display_name" => null,
                "value" => true,
                "group" => "cookie_consent",
                "order" => 1,
            ],
            [
                "key" => "cookie_consent_message",
                "display_name" => null,
                "value" => "<p>This website uses cookies to ensure you get the best experience on our website.<br><a href='#'>Learn more</a></p>",
                "group" => "cookie_consent",
                "order" => 2,
            ],
            [
                "key" => "cookie_consent_message_decline",
                "display_name" => null,
                "value" => "<p>We understand and respect your decision to decline cookie usage on our website, Thank you for visiting.</p>",
                "group" => "cookie_consent",
                "order" => 3,
            ],
            [
                "key" => "cookie_consent_redirect_decline_page_id",
                "display_name" => null,
                "value" => null,
                "group" => "cookie_consent",
                "order" => 4,
            ],
        ];

        foreach ($settings as $setting) {
            $this->createSetting($setting);
        }
    }

    private function populatePublicPageProfileSetting(): void
    {
        $role = Role::where('name', config('permission.role_names.performer'))
            ->first();

        $settings = [
            [
                "key" => "public_page_profile_slug_type",
                "display_name" => null,
                "value" => 'custom_field',
                "group" => "settings.public_page_profile_url",
                "order" => 1,
            ],
            [
                "key" => "public_page_profile_slug_custom_field",
                "display_name" => null,
                "value" => json_encode([
                    [
                        'role_id' => $role->id ?? null,
                        'field' => 'stage_name',
                    ]
                ]),
                "group" => "settings.public_page_profile_url",
                "order" => 1,
            ],
        ];

        foreach ($settings as $setting) {
            $this->createSetting($setting);
        }
    }
}
