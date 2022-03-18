<?php

namespace App\Http\Controllers\Frontend;

use App\Entities\UserMetaStripe;
use App\Http\Controllers\Controller;
use App\Http\Requests\{
    StripeAccountCreateRequest,
    StripeTransactionPaginationRequest,
};
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

    public function show(StripeTransactionPaginationRequest $request)
    {
        $user = auth()->user();
        $hasConnectedAccount = $this->stripeService->hasConnectedAccount($user);

        $pageQueryParams = null;
        $balance = null;
        $balanceTransactions = null;
        $hasPassedOnboarding = false;
        $countryOptions = [];

        if ($hasConnectedAccount) {
            $pageQueryParams = array_filter(
                $request->only('startingAfter', 'endingBefore')
            );

            $balance = $this->stripeService->accountBalance($user);

            $balanceTransactions = $this->stripeService
                ->accountBalanceTransactions(
                    $user,
                    $request->startingAfter,
                    $request->endingBefore,
                );

            $stripeAccountId = $this->stripeService->getConnectedAccountId($user);

            $stripeAccount = $this->stripeService->retrieveAccount($stripeAccountId);

            $hasPassedOnboarding = $stripeAccount->charges_enabled;

        } else {
            $countryOptions = $this->stripeService->getCountryOptions();
        }

        $defaultCountry = $this->stripeService->getDefaultCountry();

        $isEnabled = $this->stripeService->isStripeConnectEnabled($user);

        return Inertia::render('PaymentManagementStripe', compact(
            'balance',
            'balanceTransactions',
            'countryOptions',
            'defaultCountry',
            'hasConnectedAccount',
            'hasPassedOnboarding',
            'isEnabled',
            'pageQueryParams',
        ));
    }

    public function updateSetting(Request $request)
    {
        $user = auth()->user();

        $userMetaStripe = new UserMetaStripe($user);
        $userMetaStripe->setEnabledStatus($request->get('is_enabled'));

        $this->generateFlashMessage('Saved');

        return back();
    }

    public function createThenRedirect(StripeAccountCreateRequest $request)
    {
        $user = $request->user();

        $hasConnectedAccount = $this->stripeService->hasConnectedAccount($user);

        if ($hasConnectedAccount) {

            $stripeAccountId = $this->stripeService->getConnectedAccountId($user);

        } else {

            $stripeAccount = $this->stripeService->createConnectedAccount(
                $user,
                $request->get('country')
            );

            $userMetaStripe = new UserMetaStripe($user);
            $userMetaStripe->initConnectedAccount($stripeAccount);

            $stripeAccountId = $stripeAccount->id;
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

        $stripeAccountId = $this->stripeService->getConnectedAccountId($user);

        $loginLink = $this->stripeService->createLoginLink($stripeAccountId);

        return ['url' => $loginLink->url];
    }

    public function refresh()
    {
        $user = auth()->user();

        $stripeAccountId = $this->stripeService->getConnectedAccountId($user);

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

        $stripeAccountId = $this->stripeService->getConnectedAccountId($user);

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

        $stripeAccountId = $this->stripeService->getConnectedAccountId($user);

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
