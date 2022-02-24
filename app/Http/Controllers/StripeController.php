<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StripeController extends Controller
{
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

        if ($hasConnectedAccount) {
            $balance = $this->stripeService->accountBalance($user);

            $stripeAccountId = $this->stripeService->getStripeAccountId($user);

            $stripeAccount = $this->stripeService->retrieveAccount($stripeAccountId);

            $hasPassedOnboarding = $stripeAccount->charges_enabled;
        }

        return Inertia::render('PaymentManagementStripe', compact(
            'hasConnectedAccount',
            'hasPassedOnboarding',
            'balance'
        ));
    }

    public function createThenRedirect(Request $request)
    {
        $user = $request->user();

        $hasConnectedAccount = $this->stripeService->hasStripeAccount($user);

        if ($hasConnectedAccount) {

            $stripeAccountId = $this->stripeService->getStripeAccountId($user);

        } else {

            $stripeAccount = $this->stripeService->createConnectedAccount($user);
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
}
