<?php

namespace App\Services;

use App\Models\{
    Country,
    PaymentWebhook,
    Setting,
    User,
    UserMeta,
};
use App\Entities\Caches\SettingCache;
use App\Entities\UserMetaStripe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Stripe\{
    Account,
    AccountLink,
    Balance,
    Checkout\Session,
    LoginLink,
    Stripe,
    StripeClient,
    Webhook,
};
use Stripe\Exception\SignatureVerificationException;
use Symfony\Component\HttpFoundation\Response;

class StripeService
{
    private $stripeClient = null;

    private function secretKey(): string
    {
        return config('constants.stripe_sk');
    }

    private function refreshUrl(User $user): string
    {
        return URL::signedRoute('payment-management.stripe.refresh', ['user' => $user->id]);
    }

    private function returnUrl(): string
    {
        return URL::signedRoute('payment-management.stripe.return');
    }

    private function getStripeClient(): StripeClient
    {
        if (is_null($this->stripeClient)) {
            $this->stripeClient = new StripeClient($this->secretKey());
        }

        return $this->stripeClient;
    }

    public function createConnectedAccount(User $user, string $country): Account
    {
        $stripe = $this->getStripeClient();

        return $stripe->accounts->create([
            'type' => 'express',
            'business_profile' => [
                'name' => $user->fullName,
            ],
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
            'business_type' => 'individual',
            'country' => $country,
            'email' => $user->email,
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

        $stripeAccountId = $this->getConnectedAccountId($user);

        return Balance::retrieve(
            ['stripe_account' => $stripeAccountId]
        );
    }

    public function getConnectedAccountId(User $user): ?string
    {
        return (new UserMetaStripe($user))->getAccountId();
    }

    public function hasConnectedAccount(User $user): bool
    {
        return (new UserMetaStripe($user))->hasAccount();
    }

    private function getStripeAmount(float $amount, string $currency)
    {
        $formattedAmount = str_replace('.', '', $this->convertInto2Decimal($amount));

        if ($this->isZeroDecimal($currency)) {
            $formattedAmount = substr($formattedAmount, 0, -2);
        }

        return $formattedAmount;
    }

    public function checkout(User $user, float $amount, string $currency): Session
    {
        Stripe::setApiKey($this->secretKey());

        $stripeAccount = $this->getConnectedAccountId($user);

        $formattedAmount = $this->getStripeAmount(
            $this->convertInto2Decimal($amount),
            $currency
        );

        $applicationFeeAmount = $this->getStripeAmount(
            $this->getApplicationFeeAmount($amount),
            $currency
        );

        return Session::create([
            'line_items' => [[
                'name' => 'Donate to '.$user->fullName,
                'amount' => $formattedAmount,
                'currency' => $currency,
                'quantity' => 1,
            ]],
            'payment_intent_data' => [
                'application_fee_amount' => $applicationFeeAmount,
            ],
            'mode' => 'payment',
            'submit_type' => 'donate',
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

    public function retrieveAccount(string $stripeAccountId): Account
    {
        $stripe = $this->getStripeClient();

        return $stripe->accounts->retrieve($stripeAccountId);
    }

    public function setUserStripeAccount(User $user, Account $stripeAccount)
    {
        $user->setMeta('stripe_account', $stripeAccount);
        $user->saveMetas();
    }

    public function setUserStripeAccountId(User $user, string $stripeAccountId)
    {
        $user->setMeta('stripe_account_id', $stripeAccountId);
        $user->saveMetas();
    }

    private function getLogoFileFromCloud()
    {
        $url = 'https://res.cloudinary.com/bayusdb/image/upload/v1645698885/local_stripe_logo.png';

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR."stripe-account-logo.png";

        file_put_contents($path, file_get_contents($url));

        return fopen($path, 'r');
    }

    private function getLogoFile()
    {
        $path = resource_path('images/logo-128x47.png');

        return fopen($path, 'r');
    }

    private function uploadLogoFile()
    {
        $fp = $this->getLogoFile();

        $stripe = $this->getStripeClient();

        return $stripe->files->create([
            'purpose' => 'business_logo',
            'file' => $fp
        ]);
    }

    private function getPrimaryColor(): string
    {
        return '#2587BF';
    }

    private function getSecondaryColor(): string
    {
        return '#FCD42F';
    }

    public function updateAccountBrandingBasedOnPlatform(
        string $stripeAccountId
    ): Account {

        $branding = [];

        $file = $this->uploadLogoFile();

        if ($file) {
            $branding['logo'] = $file->id;
        }

        $branding['primary_color'] = $this->getPrimaryColor();
        $branding['secondary_color'] = $this->getSecondaryColor();

        return $this->updateAccount($stripeAccountId, [
            'settings' => [
                'branding' => $branding
            ],
        ]);
    }

    public function updateAccount(string $stripeAccountId, array $data): Account
    {
        $stripe = $this->getStripeClient();

        return $stripe->accounts->update(
            $stripeAccountId,
            $data
        );
    }

    public function getCountryOptions(): Collection
    {
        $countrySpecs = [];

        $countrySpecsSetting = Setting::firstOrNew([
            'key' => 'stripe_country_specs',
        ]);

        if (!$countrySpecsSetting->id) {

            $stripe = $this->getStripeClient();
            $response = $stripe->countrySpecs->all(['limit' => 100]);

            $countrySpecsSetting->value = json_encode($response->data);
            $countrySpecsSetting->save();
        }

        $countrySpecs = json_decode($countrySpecsSetting->value);
        $countrySpecs = collect($countrySpecs);
        $countryIsoIds = $countrySpecs->pluck('id');

        return Country::whereIn('alpha2', $countryIsoIds)
            ->get(['alpha2', 'display_name'])
            ->map(function ($country) {
                return [
                    'id' => $country->alpha2,
                    'value' => $country->display_name,
                ];
            });
    }

    public function getAvailableCurrencyOptions(): array
    {
        $paymentCurrencies = Setting::key('stripe_payment_currencies')
            ->value('value');

        if ($paymentCurrencies) {
            $paymentCurrencies = (array) json_decode($paymentCurrencies);

            return array_map(
                function ($currency) {
                    return [
                        'id' => $currency,
                        'value' => $currency,
                    ];
                },
                $paymentCurrencies
            );
        }
        return [];
    }

    public function getCurrencyOptions(): array
    {
        return [
            [
                'id' => 'SEK',
                'value' => 'SEK',
            ],
            [
                'id' => 'EUR',
                'value' => 'EUR',
            ],
            [
                'id' => 'GBP',
                'value' => 'GBP',
            ],
            [
                'id' => 'USD',
                'value' => 'USD',
            ],
        ];
    }

    public function getAmountOptions(): array
    {
        $currencyAmountOptions = Setting::key('stripe_amount_options')
            ->value('value');

        return (
            $currencyAmountOptions
            ? (array) json_decode($currencyAmountOptions)
            : []
        );
    }

    public function getApplicationFeeAmount($amount): float
    {
        return round($amount * config('constants.stripe_fee_percent'), 2);
    }

    public function getCurrencyMinimalPayment(string $currency): float
    {
        return (float) config('constants.stripe_minimal_payments.'.$currency) ?? 0;
    }

    public function getMinimalPaymentWithFee($amount): float
    {
        return ceil($amount + $amount * $this->getApplicationFeeAmount($amount));
    }

    public function getListMinimalPayments(): array
    {
        return array_map(
            fn ($amount): float => $this->getMinimalPaymentWithFee($amount),
            config('constants.stripe_minimal_payments')
        );
    }

    public function getDefaultCountry(): ?string
    {
        return Setting::key('stripe_default_country')->value('value');
    }

    public function isEnabled(): bool
    {
        return app(SettingCache::class)->remember('stripe_is_enabled', function () {
            return (bool) Setting::key('stripe_is_enabled')->value('value');
        });
    }

    public function isStripeConnectEnabled(User $user): bool
    {
        $key = 'stripe_is_enabled';

        return (bool) $user->getMetas([$key])->get($key, false);
    }

    private function getUserIdFromStripeAccount(string $stripeAccount): ?int
    {
        return UserMeta::keyAndValue('stripe_account_id', $stripeAccount)
            ->value('user_id');
    }

    public function webhook($payload, $stripeSignature): Response
    {
        $event = null;

        $endpointSecret = config('constants.stripe_endpoint_secret');

        Stripe::setApiKey($this->secretKey());

        // @see https://stripe.com/docs/webhooks/signatures for more information.
        try {
            $event = Webhook::constructEvent(
                $payload,
                $stripeSignature,
                $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            return response(
                '⚠️  Webhook error while validating payload.',
                400
            );
        } catch (SignatureVerificationException $e) {
            return response(
                '⚠️  Webhook error while validating signature.',
                400
            );
        }

        $webhookData = [
            'data' => $payload,
            'payment_method' => PaymentWebhook::PAYMENT_METHOD_STRIPE,
            'event_type' => $event->type,
        ];

        if (!empty($event->account)) {
            $webhookData['receiver_id'] = $this->getUserIdFromStripeAccount(
                $event->account
            );
        }

        if ($event->type == 'checkout.session.completed') {
            // TODO trigger email
        }

        PaymentWebhook::create($webhookData);

        return response('Success', 200);
    }

    public function testTransactionList(/* User $user */)
    {
        Stripe::setApiKey($this->secretKey());

        //$stripeAccountId = $this->getStripeAccountId($user);
        $stripeAccountId = 'acct_1KZRrDR8AGiJWqnO';

        return \Stripe\BalanceTransaction::all(
            ['limit' => 3],
            ['stripe_account' => $stripeAccountId]
        );
    }
}
