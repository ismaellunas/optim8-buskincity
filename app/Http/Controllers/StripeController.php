<?php

namespace App\Http\Controllers;

use App\Http\Requests\StripeSettingRequest;
use App\Jobs\{
    UpdateStripeConnectedAccountColor,
    UpdateStripeConnectedAccountBrandingLogo,
};
use App\Services\{
    MediaService,
    StripeService,
    StripeSettingService,
};
use App\Traits\FlashNotifiable;
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
        $user = auth()->user();
        $settings = $this->stripeSettingService->getAll();

        $currencyOptions = collect($this->stripeService->getCurrencyOptions())
            ->map(function ($option) {
                return $option['id'];
            });

        $logoMedia = $this->stripeSettingService->logoMedia();

        return Inertia::render('Stripe', [
            'amountOptions' => $settings->get('stripe_amount_options'),
            'applicationFeePercentage' => $settings->get('stripe_application_fee_percentage'),
            'can' => [
                'media' => [
                    'read' => $user->can('media.read'),
                    'add' => $user->can('media.add'),
                ]
            ],
            'colorPrimary' => $settings->get('stripe_color_primary'),
            'colorSecondary' => $settings->get('stripe_color_secondary'),
            'countryOptions' => $this->stripeService->getCountryOptions(),
            'currencyOptions' => $currencyOptions,
            'defaultCountry' => $settings->get('stripe_default_country'),
            'isEnabled' => $settings->get('stripe_is_enabled'),
            'instructions' => [
                'mediaLibrary' => MediaService::defaultMediaLibraryInstructions(),
            ],
            'logoMedia' => $logoMedia,
            'minimalAmounts' => $settings->get('stripe_minimal_amounts'),
            'paymentCurrencies' => $settings->get('stripe_payment_currencies'),
            'title' => __('Stripe'),
            'i18n' => $this->translations(),
        ]);
    }

    public function update(StripeSettingRequest $request)
    {
        $changedKeys = collect();
        $colorKeys = [
            'color_primary',
            'color_secondary',
        ];

        $inputs = $request->validated();

        foreach ($inputs as $key => $setting) {
            if (! in_array($key, ['logo'])) {
                $setting = $this->stripeSettingService->save('stripe_'.$key, $setting);

                if ($setting->wasChanged()) {
                    $changedKeys->push($key);
                }
            }
        }

        if ($changedKeys->intersect($colorKeys)->isNotEmpty()) {
            $job = new UpdateStripeConnectedAccountColor(
                $request->get('color_primary'),
                $request->get('color_secondary')
            );

            $job->delay(now()->addSeconds(30));

            dispatch($job);
        }

        if ($request->has('logo')) {
            $logoMediaId = $request->logo;

            $this->stripeSettingService->saveLogoMedia($logoMediaId);

            $job = new UpdateStripeConnectedAccountbrandingLogo();
            $job->delay(now()->addMinutes(1));

            dispatch($job);
        }

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Stripe')
        ]);

        return redirect()->back();
    }

    private function translations(): array
    {
        return [
            ...[
                'settings' => __('Settings'),
                'is_enabled' => __('Is Enabled?'),
                'default_country' => __('Default Country'),
                'application_fee_percentage' => __('Application Fee Percentage'),
                'payment_currencies' => __('Payment Currencies'),
                'primary_color' => __('Primary Color'),
                'secondary_color' => __('Secondary Color'),
                'logo' => __('Logo'),
                'open_media_library' => __('Open Media Library'),
                'save' => __('Save'),
                'currency' => __('Currency'),
                'minimal_payment' => __('Minimal Payment'),
                'amount_options' => __('Amount Options'),
            ],
            ...MediaService::defaultMediaLibraryTranslations(),
        ];
    }
}
