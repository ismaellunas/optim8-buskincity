<?php

namespace App\Http\Controllers\Frontend;

use App\Entities\UserMetaStripe;
use App\Http\Controllers\Controller;
use App\Http\Requests\{
    StripeAccountCreateRequest,
    StripeFrontendSettingRequest,
    StripeTransactionPaginationRequest,
};
use App\Services\{
    IPService,
    StripeService,
    StripeSettingService,
};
use App\Traits\FlashNotifiable;
use Exception;
use Inertia\Inertia;
use Stripe\Exception\ApiErrorException;

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

    public function show(StripeTransactionPaginationRequest $request)
    {
        $user = auth()->user();
        $hasConnectedAccount = $this->getUserMetaStripe()->hasAccount();

        $pageQueryParams = null;
        $balance = null;
        $balanceTransactions = null;
        $hasPassedOnboarding = false;
        $countryOptions = [];
        $defaultCountry = null;
        $hasPassedOnboarding = false;

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

            $stripeAccountId = $this->getUserMetaStripe()->getAccountId();

            $stripeAccount = $this->stripeService->retrieveAccount($stripeAccountId);

            $hasPassedOnboarding = $stripeAccount->charges_enabled;

        } else {
            $countryOptions = $this->stripeService->getCountryOptions();

            $defaultCountry = app(IPService::class)->getCountryCode(
                app(StripeSettingService::class)->getDefaultCountry()
            );
        }

        return Inertia::render('StripeConnect', [
            'balance' => $balance,
            'balanceTransactions' => $balanceTransactions,
            'countryOptions' => $countryOptions,
            'defaultCountry' => $defaultCountry,
            'hasConnectedAccount' => $hasConnectedAccount,
            'hasPassedOnboarding' => $hasPassedOnboarding,
            'isEnabled' => $this->getUserMetaStripe()->isEnabled(),
            'pageQueryParams' => $pageQueryParams,
            'title' => 'Stripe Connect',
        ]);
    }

    public function updateSetting(StripeFrontendSettingRequest $request)
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
        $redirect = redirect()->route('payments.stripe.show');

        $stripeAccountId = $this->getUserMetaStripe()->getAccountId();

        try {
            $stripeAccount = $this
                ->stripeService
                ->updateAccountBrandingBasedOnPlatform($stripeAccountId);

            $this->getUserMetaStripe()->setAccount($stripeAccount);

            return $redirect
                ->with('message', 'Stripe Account created successfully.');

        } catch (ApiErrorException | Exception $e) {

            return $redirect->withError($e->getMessage());
        }
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
}
