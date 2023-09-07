<?php

namespace App\Http\Controllers\Frontend;

use App\Entities\UserMetaStripe;
use App\Http\Controllers\Controller;
use App\Services\{
    IPService,
    StripeService,
    StripeSettingService,
};
use App\Traits\FlashNotifiable;
use Inertia\Inertia;

class PaymentController extends Controller
{
    use FlashNotifiable;

    private $stripeService;
    private $userMetaStripe;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    private function getUserMetaStripe()
    {
        if (is_null($this->userMetaStripe)) {
            $this->userMetaStripe = new UserMetaStripe(auth()->user());
        }

        return $this->userMetaStripe;
    }

    public function index()
    {
        $hasConnectedAccount = $this->getUserMetaStripe()->hasAccount();

        $countryOptions = $this->stripeService->getCountryOptions();

        $defaultCountry = app(IPService::class)->getCountryCode(
            app(StripeSettingService::class)->getDefaultCountry()
        );

        return Inertia::render('Payments', [
            'countryOptions' => $countryOptions,
            'defaultCountry' => $defaultCountry,
            'hasConnectedAccount' => $hasConnectedAccount,
            'title' => __('Payments'),
            'i18n' => $this->i18n(),
        ]);
    }

    private function i18n(): array
    {
        return [
            'connect_payment' => __('Connect Payments with Stripe'),
            'inconnect' => __(
                'If you would like to receive donations and payments for private gigs through :appName, please apply for payments with Stripe:',
                [
                    'appName' => config('app.name'),
                ]
            ),
            'country' => __('Country'),
            'select_option' => __('Select option'),
            'create_connected_account' => __('Create connected account'),
            'connect' => __(
                'Visit the payment’s dashboard to manage your Stripe Connect account.'
            ),
            'manage_payments' => __('Manage payments'),
            'create_alert' => [
                'title' => __('Please double-check your country!'),
                'text' => __('You will not be able to change your country in the future.'),
                'button' => __('Continue'),
            ],
        ];
    }
}
