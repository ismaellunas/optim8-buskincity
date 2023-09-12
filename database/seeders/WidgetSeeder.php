<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WidgetSeeder extends Seeder
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
                "key" => "dashboard_widget",
                "display_name" => null,
                "value" => json_encode([
                    [
                        'id' => Str::uuid(),
                        'widget' => "App\Entities\Widgets\QrCodeWidget",
                        'module' => null,
                        'title' => 'Your QR code',
                        'order' => 1,
                        'grid' => [
                            'desktop' => 6,
                            'tablet' => 6,
                            'mobile' => 12,
                        ],
                        'setting' => [],
                        'i18n' => [
                            'description' => 'Print your QR code and place it on your pitch. It will allow your audience to find you on :appName, send donations, book you for private gigs, or follow your work.',
                            'button_print' => 'Print',
                            'button_download' => 'Download',
                        ],
                        'is_enabled' => true,
                    ],
                    [
                        'id' => Str::uuid(),
                        'widget' => "App\Entities\Widgets\SocialMediaShareWidget",
                        'module' => null,
                        'title' => 'Share your page',
                        'order' => 2,
                        'grid' => [
                            'desktop' => 6,
                            'tablet' => 6,
                            'mobile' => 12,
                        ],
                        'setting' => [],
                        'i18n' => [
                            'description' => 'As a :role, you have a public page to share with your audience. It\'s just like your unique site within :appName. You can copy the link or share on your social media:',
                            'button_view' => 'View page'
                        ],
                        'is_enabled' => true,
                    ],
                    [
                        'id' => Str::uuid(),
                        'widget' => "App\Entities\Widgets\StripeConnectWidget",
                        'module' => null,
                        'title' => 'Connect Payments with Stripe',
                        'order' => 3,
                        'grid' => [
                            'desktop' => 6,
                            'tablet' => 6,
                            'mobile' => 12,
                        ],
                        'setting' => [],
                        'i18n' => [
                            'inconnect' => 'If you would like to receive donations and payments for private gigs through :appName, please apply for payments with Stripe:',
                            'country' => 'Country',
                            'select_an_option' => 'Select an option',
                            'create_a_connected_account' => 'Create a connected account',
                            'connect' => 'Visit the payment’s dashboard to manage your Stripe Connect account.',
                            'manage_payments' => 'Manage payments',
                            'create_alert_title' => 'Please double-check your country!',
                            'create_alert_text' => 'You will not be able to change your country in the future.',
                            'create_alert_button' => 'Continue',
                        ],
                        'is_enabled' => true,
                    ],
                    [
                        'id' => Str::uuid(),
                        'widget' => "App\Entities\Widgets\DefaultWidget",
                        'module' => 'Booking',
                        'title' => 'Book a product',
                        'order' => 4,
                        'grid' => [
                            'desktop' => 6,
                            'tablet' => 6,
                            'mobile' => 12,
                        ],
                        'setting' => [
                            'url' => route('booking.products.index'),
                        ],
                        'i18n' => [
                            'description' => ':appName collaborates with cities to enable you to effortlessly book your own tours',
                            'button_url' => 'Book now',
                        ],
                        'is_enabled' => true,
                    ],
                    [
                        'id' => Str::uuid(),
                        'widget' => "Modules\Booking\Widgets\MyBookingWidget",
                        'module' => 'Booking',
                        'title' => 'My bookings',
                        'order' => 6,
                        'grid' => [
                            'desktop' => 12,
                            'tablet' => 12,
                            'mobile' => 12,
                        ],
                        'setting' => [],
                        'i18n' => [
                            'product' => 'Product',
                            'city' => 'City',
                            'country' => 'Country',
                            'time' => 'Time',
                            'actions' => 'Actions',
                            'empty' => 'Empty',
                            'button_more' => 'More',
                        ],
                        'is_enabled' => true,
                    ],
                    [
                        'id' => Str::uuid(),
                        'widget' => "App\Entities\Widgets\DefaultWidget",
                        'module' => 'Booking',
                        'title' => 'Upcoming Events',
                        'order' => 7,
                        'grid' => [
                            'desktop' => 12,
                            'tablet' => 12,
                            'mobile' => 12,
                        ],
                        'setting' => [
                            'url' => "",
                            'visibility' => [
                                'role' => [],
                            ]
                        ],
                        'i18n' => [
                            'description' => 'Coming soon! We are brewing a new feature that will allow you to see nearby city events and performances. Have questions?',
                            'button_url' => 'Get in touch',
                        ],
                        'is_enabled' => true,
                    ],
                    [
                        'id' => Str::uuid(),
                        'widget' => "App\Entities\Widgets\DefaultWidget",
                        'module' => null,
                        'title' => 'Street Performers You Might Like',
                        'order' => 8,
                        'grid' => [
                            'desktop' => 12,
                            'tablet' => 12,
                            'mobile' => 12,
                        ],
                        'setting' => [
                            'url' => "",
                            'visibility' => [
                                'role' => [],
                            ]
                        ],
                        'i18n' => [
                            'description' => 'Coming soon! We are brewing a new feature that will allow you to follow your favourite street performers. Have questions?',
                            'button_url' => 'Get in touch',
                        ],
                        'is_enabled' => true,
                    ],
                    [
                        'id' => Str::uuid(),
                        'widget' => "App\Entities\Widgets\DefaultWidget",
                        'module' => null,
                        'title' => 'Want to Become a Street Performer?',
                        'order' => 8,
                        'grid' => [
                            'desktop' => 12,
                            'tablet' => 12,
                            'mobile' => 12,
                        ],
                        'setting' => [
                            'url' => "",
                            'visibility' => [
                                'role' => [],
                            ]
                        ],
                        'i18n' => [
                            'description' => 'Coming soon! We are brewing a new feature that will allow you to have access to educational materials to help you become a successful screet performer. Have questions?',
                            'button_url' => 'Get in touch',
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
