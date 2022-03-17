<?php

namespace App\Http\Controllers\Frontend;

use App\Entities\UserMetaStripe;
use App\Http\Controllers\Controller;
use App\Http\Requests\StripeAccountCreateRequest;
use App\Services\StripeService;
use App\Traits\FlashNotifiable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StripeController extends Controller
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

    public function show()
    {
        $user = auth()->user();
        $hasConnectedAccount = $this->getUserMetaStripe()->hasAccount();

        $balance = null;
        $hasPassedOnboarding = false;
        $countryOptions = [];

        if ($hasConnectedAccount) {
            $balance = $this->stripeService->accountBalance($user);

            $stripeAccountId = $this->getUserMetaStripe()->getAccountId();

            $stripeAccount = $this->stripeService->retrieveAccount($stripeAccountId);

            $hasPassedOnboarding = $stripeAccount->charges_enabled;

        } else {
            $countryOptions = $this->stripeService->getCountryOptions();
        }

        return Inertia::render('PaymentManagementStripe', [
            'balance' => $balance,
            'countryOptions' => $countryOptions,
            'defaultCountry' => $this->stripeService->getDefaultCountry(),
            'hasConnectedAccount' => $hasConnectedAccount,
            'hasPassedOnboarding' => $hasPassedOnboarding,
            'isEnabled' => $this->getUserMetaStripe()->isEnabled(),
        ]);
    }

    public function updateSetting(Request $request)
    {
        $this->getUserMetaStripe()->setEnabledStatus($request->get('is_enabled'));

        $this->generateFlashMessage('Saved');

        return back();
    }

    private function redirectToAccountLink(string $stripeAccountId)
    {
        $accountLink = $this->stripeService->createAccountLink(
            $stripeAccountId,
            auth()->user()
        );

        return Inertia::location($accountLink->url);
    }

    public function createThenRedirect(StripeAccountCreateRequest $request)
    {
        $hasConnectedAccount = $this->getUserMetaStripe()->hasAccount();

        if ($hasConnectedAccount) {

            $stripeAccountId = $this->getUserMetaStripe()->getAccountId();

        } else {

            $stripeAccount = $this->stripeService->createConnectedAccount(
                auth()->user(),
                $request->get('country')
            );

            $this->getUserMetaStripe()->initConnectedAccount($stripeAccount);

            $stripeAccountId = $stripeAccount->id;
        }

        return $this->redirectToAccountLink($stripeAccountId);
    }

    public function redirectToStripeAccount()
    {
        $stripeAccountId = $this->getUserMetaStripe()->getAccountId();

        $loginLink = $this->stripeService->createLoginLink($stripeAccountId);

        return ['url' => $loginLink->url];
    }

    public function refresh()
    {
        $stripeAccountId = $this->getUserMetaStripe()->getAccountId();

        $stripeAccount = $this->stripeService->retrieveAccount($stripeAccountId);

        $this->getUserMetaStripe()->setAccount($stripeAccount);

        $this->redirectToAccountLink($stripeAccount->id);
    }

    public function return()
    {
        $stripeAccountId = $this->getUserMetaStripe()->getAccountId();

        $stripeAccount = $this
            ->stripeService
            ->updateAccountBrandingBasedOnPlatform($stripeAccountId);

        $this->getUserMetaStripe()->setAccount($stripeAccount);

        return redirect()
            ->route('payment-management.stripe.show')
            ->with('message', 'Stripe Account created successfully.');
    }

    public function accountLink()
    {
        $stripeAccountId = $this->getUserMetaStripe()->getAccountId();

        $accountLink = $this->stripeService->createAccountLink(
            $stripeAccountId,
            auth()->user()
        );

        return ['url' => $accountLink->url];
    }

    public function webhook(Request $request)
    {
        return $this->stripeService->webhook(
            $request->getContent(),
            $request->server('HTTP_STRIPE_SIGNATURE')
        );
    }
}
