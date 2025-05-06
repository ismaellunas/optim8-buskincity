<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class StripeSeeder extends Seeder
{
    public function run()
    {
        $paymentCurrencies = config('constants.stripe_payment_currencies');
        $minimalPayments = config('constants.stripe_minimal_payments');

        $minimalAmounts = [];

        foreach ($paymentCurrencies as $currency) {
            $amount = $minimalPayments[$currency];
            $amount = ceil($amount + $amount * 10 / 100);

            $minimalAmounts[$currency] = $amount;
        }

        $steps = [1, 2, 3, 5, 10];
        $amountOptions = [];

        foreach ($paymentCurrencies as $currency) {
            foreach ($steps as $step) {
                $amountOptions[$currency][] = $minimalAmounts[$currency] * $step;
            }
        }

        $settings = [
            [
                "key" => "stripe_is_enabled",
                "display_name" => "Is Enabled",
                "value" => true,
            ],
            [
                "key" => "stripe_application_fee_percentage",
                "display_name" => "Application Fee Percentage",
                "value" => config('constants.stripe_fee_percent') * 100,
            ],
            [
                "key" => "stripe_payment_currencies",
                "display_name" => "Payment Currencies",
                "value" => json_encode($paymentCurrencies),
            ],
            [
                "key" => "stripe_minimal_amounts",
                "display_name" => "Minimal Amounts",
                "value" => json_encode($minimalAmounts),
            ],
            [
                "key" => "stripe_amount_options",
                "display_name" => "Amount Options",
                "value" => json_encode($amountOptions),
            ],
            [
                "key" => "stripe_default_country",
                "display_name" => "Default Country",
                "value" => "SE",
            ],
            [
                "key" => "stripe_country_specs",
                "display_name" => "Country Specifications",
                "value" => null,
            ],
            [
                "key" => "stripe_color_primary",
                "display_name" => "Primary Color",
                "value" => "#395dbf",
            ],
            [
                "key" => "stripe_color_secondary",
                "display_name" => "Secondary Color",
                "value" => "#fcd42f",
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
                    "group" => "stripe",
                    "created_at" => now(),
                    "updated_at" => now(),
                ]
            );
        }
    }
}
