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
            'title' => 'Payments',
        ]);
    }
}
