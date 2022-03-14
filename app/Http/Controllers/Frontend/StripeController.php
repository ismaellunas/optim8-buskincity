<?php

namespace App\Http\Controllers\Frontend;

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

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function show()
    {
        $user = auth()->user();
        $hasConnectedAccount = $this->stripeService->hasStripeAccount($user);

        $balance = null;
        $hasPassedOnboarding = false;
        $countryOptions = [];

        if ($hasConnectedAccount) {
            $balance = $this->stripeService->accountBalance($user);

            $stripeAccountId = $this->stripeService->getStripeAccountId($user);

            $stripeAccount = $this->stripeService->retrieveAccount($stripeAccountId);

            $hasPassedOnboarding = $stripeAccount->charges_enabled;

        } else {
            $countryOptions = $this->stripeService->getCountryOptions();
        }

        $defaultCountry = $this->stripeService->getDefaultCountry();

        $isEnabled = $this->stripeService->isStripeConnectEnabled($user);

        return Inertia::render('PaymentManagementStripe', compact(
            'balance',
            'countryOptions',
            'defaultCountry',
            'hasConnectedAccount',
            'hasPassedOnboarding',
            'isEnabled'
        ));
    }

    public function updateSetting(Request $request)
    {
        $user = auth()->user();

        $user->setMeta('stripe_is_enabled', $request->get('is_enabled'));
        $user->saveMetas();

        $this->generateFlashMessage('Saved');

        return back();
    }

    public function createThenRedirect(StripeAccountCreateRequest $request)
    {
        $user = $request->user();

        $hasConnectedAccount = $this->stripeService->hasStripeAccount($user);

        if ($hasConnectedAccount) {

            $stripeAccountId = $this->stripeService->getStripeAccountId($user);

        } else {

            $stripeAccount = $this->stripeService->createConnectedAccount(
                $user,
                $request->get('country')
            );
            $stripeAccountId = $stripeAccount->id;

            $user->setMeta('stripe_account', $stripeAccount);
            $user->setMeta('stripe_account_id', $stripeAccount->id);
            $user->saveMetas();
        }

        $accountLink = $this->stripeService->createAccountLink(
            $stripeAccountId,
            $user
        );

        return Inertia::location($accountLink->url);
    }

    public function redirectToStripeAccount()
    {
        $user = auth()->user();

        $stripeAccountId = $this->stripeService->getStripeAccountId($user);

        $loginLink = $this->stripeService->createLoginLink($stripeAccountId);

        return ['url' => $loginLink->url];
    }

    public function refresh()
    {
        $user = auth()->user();

        $stripeAccountId = $this->stripeService->getStripeAccountId($user);

        $stripeAccount = $this->stripeService->retrieveAccount($stripeAccountId);

        $this->stripeService->setUserStripeAccount($user, $stripeAccount);

        $accountLink = $this->stripeService->createAccountLink(
            $stripeAccount->id,
            $user
        );

        return redirect()->away($accountLink->url);
    }

    public function return()
    {
        $user = auth()->user();

        $stripeAccountId = $this->stripeService->getStripeAccountId($user);

        $stripeAccount = $this
            ->stripeService
            ->updateAccountBrandingBasedOnPlatform($stripeAccountId);

        $this->stripeService->setUserStripeAccount($user, $stripeAccount);

        return redirect()
            ->route('payment-management.stripe.show')
            ->with('message', 'Stripe Account created successfully.');
    }

    public function accountLink()
    {
        $user = auth()->user();

        $stripeAccountId = $this->stripeService->getStripeAccountId($user);

        $accountLink = $this->stripeService->createAccountLink(
            $stripeAccountId,
            $user
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
