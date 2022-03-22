<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use App\Services\StripeSettingService;
use App\Traits\FlashNotifiable;
use App\Http\Requests\StripeSettingRequest;
use Inertia\Inertia;

class StripeController extends Controller
{
    use FlashNotifiable;

    private $stripeService;
    private $stripeSettingService;

    public function __construct(
        StripeService $stripeService,
        StripeSettingService $stripeSettingService
    ) {
        $this->stripeService = $stripeService;
        $this->stripeSettingService = $stripeSettingService;
    }

    public function edit()
    {
        $settings = $this->stripeSettingService->getAll();

        $currencyOptions = collect($this->stripeService->getCurrencyOptions())
            ->map(function ($option) {
                return $option['id'];
            });

        return Inertia::render('Stripe', [
            'amountOptions' => $settings->get('stripe_amount_options'),
            'applicationFeePercentage' => $settings->get('stripe_application_fee_percentage'),
            'countryOptions' => $this->stripeService->getCountryOptions(),
            'currencyOptions' => $currencyOptions,
            'defaultCountry' => $settings->get('stripe_default_country'),
            'isEnabled' => $settings->get('stripe_is_enabled'),
            'minimalAmounts' => $settings->get('stripe_minimal_amounts'),
            'paymentCurrencies' => $settings->get('stripe_payment_currencies'),
        ]);
    }

    public function update(StripeSettingRequest $request)
    {
        foreach ($request->validated() as $key => $setting) {
            $this->stripeSettingService->save('stripe_'.$key, $setting);
        }

        $this->generateFlashMessage('Stripe updated successfully!');

        return redirect()->back();
    }
}
