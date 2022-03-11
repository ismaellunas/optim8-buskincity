<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\StripeService;
use App\Traits\FlashNotifiable;
use App\Http\Requests\StripeSettingRequest;
use Inertia\Inertia;

class StripeController extends Controller
{
    use FlashNotifiable;

    private $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function edit()
    {
        $settings = Setting::whereIn('key', [
            'stripe_amount_options',
            'stripe_application_fee_percentage',
            'stripe_default_country',
            'stripe_is_enabled',
            'stripe_minimal_amounts',
            'stripe_payment_currencies',
        ])
            ->select(['key', 'value'])
            ->pluck('value', 'key');

        $currencyOptions = collect($this->stripeService->getCurrencyOptions())
            ->map(function ($option) {
                return $option['id'];
            });

        return Inertia::render('Stripe', [
            'amountOptions' => (object)json_decode($settings->get('stripe_amount_options')),
            'applicationFeePercentage' => (float) $settings->get('stripe_application_fee_percentage'),
            'countryOptions' => $this->stripeService->getCountryOptions(),
            'currencyOptions' => $currencyOptions,
            'defaultCountry' => $settings->get('stripe_default_country'),
            'isEnabled' => (bool) $settings->get('stripe_is_enabled'),
            'minimalAmounts' => json_decode($settings->get('stripe_minimal_amounts')),
            'paymentCurrencies' => json_decode($settings->get('stripe_payment_currencies')),
        ]);
    }

    public function update(StripeSettingRequest $request)
    {
        Setting::updateOrCreate(
            ['key' => 'stripe_payment_currencies'],
            ['value' => json_encode($request->payment_currencies)]
        );
        Setting::updateOrCreate(
            ['key' => 'stripe_default_country'],
            ['value' => $request->default_country]
        );
        Setting::updateOrCreate(
            ['key' => 'stripe_application_fee_percentage'],
            ['value' => $request->application_fee_percentage]
        );
        Setting::updateOrCreate(
            ['key' => 'stripe_amount_options'],
            ['value' => json_encode($request->amount_options)]
        );
        Setting::updateOrCreate(
            ['key' => 'stripe_minimal_amounts'],
            ['value' => json_encode($request->minimal_amounts)]
        );
        Setting::updateOrCreate(
            ['key' => 'stripe_is_enabled'],
            ['value' => (bool) $request->is_enabled]
        );

        $this->generateFlashMessage('Stripe updated successfully!');

        return redirect()->back();
    }
}
