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
        if ($hasConnectedAccount) {
            $balance = $this->stripeService->accountBalance($user);
        }

        return Inertia::render('PaymentManagementStripe', compact(
            'hasConnectedAccount',
            'balance'
        ));
    }

    public function createThenRedirect(Request $request)
    {
        $user = $request->user();

        $connectedAccount = $this->stripeService->createConnectedAccount($user);

        $user->setMeta('stripe_account_id', $connectedAccount->id);
        $user->saveMetas();

        $accountLink = $this->stripeService->createAccountLink(
            $connectedAccount->id,
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
}
