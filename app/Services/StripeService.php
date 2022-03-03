<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Stripe\{
    Account,
    AccountLink,
    Balance,
    Checkout\Session,
    LoginLink,
    Stripe,
    StripeClient,
};

class StripeService
{
    private $stripeClient = null;

    private function metaKey(): string
    {
        return config('constants.stripe_account_id_meta_key');
    }

    private function secretKey(): string
    {
        return config('constants.stripe_sk');
    }

    private function refreshUrl(User $user): string
    {
        return URL::signedRoute('payments.stripe.refresh', ['user' => $user->id]);
    }

    private function returnUrl(User $user): string
    {
        return URL::signedRoute('payments.stripe.return', ['user' => $user->id]);
    }

    private function getStripeClient(): StripeClient
    {
        if (is_null($this->stripeClient)) {
            $this->stripeClient = new StripeClient($this->secretKey());
        }

        return $this->stripeClient;
    }

    public function createConnectedAccount(User $user): Account
    {
        $stripe = $this->getStripeClient();

        return $stripe->accounts->create([
            //'type' => 'standard',
            'type' => 'express',
            'business_profile' => [
                'name' => $user->fullName,
            ],
            'country' => 'SE',
        ]);
    }

    public function createAccountLink(string $connectedAccountId, User $user): AccountLink
    {
        $stripe = $this->getStripeClient();

        return $stripe->accountLinks->create([
            'account' => $connectedAccountId,
            'refresh_url' => $this->refreshUrl($user),
            'return_url' => $this->returnUrl($user),
            'type' => 'account_onboarding',
        ]);
    }

    public function createLoginLink(string $connectedAccountId): LoginLink
    {
        Stripe::setApiKey($this->secretKey());

        return Account::createLoginLink($connectedAccountId);
    }

    public function accountBalance(User $user): Balance
    {
        Stripe::setApiKey($this->secretKey());

        $stripeAccountId = $this->getStripeAccountId($user);

        return Balance::retrieve(
            ['stripe_account' => $stripeAccountId]
        );
    }

    public function getStripeAccountId(User $user): string
    {
        return $user->getMetas([$this->metaKey()])->get($this->metaKey());
    }

    public function hasStripeAccount(User $user): bool
    {
        return $user->getMetas([$this->metaKey()])->isNotEmpty();
    }

    public function checkout(User $user, $amount): Session
    {
        Stripe::setApiKey($this->secretKey());

        $currency = 'eur';

        $stripeAccount = $this->getStripeAccountId($user);

        $formattedAmount = str_replace('.', '', $this->convertInto2Decimal($amount));

        if ($this->isZeroDecimal($currency)) {
            $formattedAmount = substr($formattedAmount, 0, -2);
        }

        return Session::create([
            'line_items' => [[
                'name' => 'Donation',
                'amount' => $formattedAmount,
                'currency' => $currency,
                'quantity' => 1,
            ]],
            /*
            'payment_intent_data' => [
                'application_fee_amount' => 123,
            ],
             */
            'mode' => 'payment',
            'payment_method_types' => ['card', 'giropay', 'ideal'],
            'success_url' => route('donations.success', ['user' => $user]),
            'cancel_url' => route('frontend.profiles', ['user' => $user]),
        ], [
            'stripe_account' => $stripeAccount,
        ]);
    }

    private function convertInto2Decimal($amount): string
    {
        return number_format((float)$amount, 2, '.', '');
    }

    private function isZeroDecimal(string $currency): bool
    {
        return in_array(strtoupper($currency), [
            'BIF',
            'CLP',
            'DJF',
            'GNF',
            'JPY',
            'KMF',
            'KRW',
            'MGA',
            'PYG',
            'RWF',
            'UGX',
            'VND',
            'VUV',
            'XAF',
            'XOF',
            'XPF',
        ]);
    }
}
