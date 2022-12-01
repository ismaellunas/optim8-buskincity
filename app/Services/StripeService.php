<?php

namespace App\Services;

use App\Entities\Caches\SettingCache;
use App\Entities\UserMetaStripe;
use App\Helpers\HumanReadable;
use App\Mail\ThankYouCheckoutCompleted;
use App\Models\{
    Country,
    Media,
    PaymentWebhook,
    User,
    UserMeta,
};
use App\Services\SettingService;
use Cloudinary\Transformation\Resize;
use Illuminate\Support\{
    Collection,
    Facades\Mail,
    Facades\URL,
    Str,
};
use Stripe\{
    Account,
    AccountLink,
    Balance,
    BalanceTransaction,
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
    private $perPage = 10;
    private $keys = null;

    const BRAND_HEIGHT = 128;
    const BRAND_WIDTH = 128;

    private function getKeys(): array
    {
        if (is_null($this->keys)) {
            $this->keys = app(SettingService::class)->getStripeKeys();
        }

        return $this->keys;
    }

    private function secretKey(): ?string
    {
        return $this->getKeys()['stripe_sk'] ?? null;
    }

    private function refreshUrl(User $user): string
    {
        return URL::signedRoute('payments.stripe.refresh', ['user' => $user->id]);
    }

    private function returnUrl(): string
    {
        return URL::signedRoute('payments.stripe.return');
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

        $accountData = [
            'type' => 'express',
            'business_profile' => [
                'name' => $user->fullName,
            ],
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
            'settings' => [
                'payouts' => [
                    'debit_negative_balances' => false,
                ],
            ],
            'business_type' => 'individual',
            'country' => $country,
            'email' => $user->email,
        ];

        return $stripe->accounts->create($accountData);
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

    public function accountBalanceTransactions(
        User $user,
        string $startingAfter = null,
        string $endingBefore = null,
    ) {
        Stripe::setApiKey($this->secretKey());

        $connectedAccountId = $this->getConnectedAccountId($user);

        $transactions = BalanceTransaction::all(
            [
                'limit' => $this->perPage,
                'starting_after' => $startingAfter,
                'ending_before' => $endingBefore,
            ],
            ['stripe_account' => $connectedAccountId]
        );

        $this->reFormatTransactionData($transactions);
        $this->setPaginateUrlTransaction($transactions);

        return $transactions;
    }

    private function reFormatTransactionData(&$transactions): void
    {
        $transactions['data'] = collect($transactions['data'])
            ->map(function ($transaction) {
                $amount = $transaction->net;

                if (!$this->isZeroDecimal($transaction->currency)) {
                    $amount = $amount / 100;
                }

                return [
                    'id' => $transaction->id,
                    'currency' => Str::upper($transaction->currency),
                    'amount' => $amount,
                    'created' => HumanReadable::timestampToDateTime($transaction->created),
                ];
            });
    }

    private function setPaginateUrlTransaction(&$transactions): void
    {
        $transactions['next_url'] = null;
        $transactions['previous_url'] = null;

        $totalData = $transactions['data']->count();

        if ($totalData > 0) {
            $transactions['next_url'] = route(
                    'payments.stripe.show',
                    [
                        'startingAfter' => $transactions['data'][$totalData - 1]['id'],
                    ]
                );

            $transactions['previous_url'] = route(
                    'payments.stripe.show',
                    [
                        'endingBefore' => $transactions['data'][0]['id'],
                    ]
                );
        }
    }

    private function getConnectedAccountId(User $user): ?string
    {
        return (new UserMetaStripe($user))->getAccountId();
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
            'cancel_url' => $user->profilePageUrl
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

    public function getLogoFileFromCloud(Media $logoMedia): mixed
    {
        $result = cloudinary()
            ->getImageTag($logoMedia->file_name)
            ->version($logoMedia->version)
            ->resize(
                Resize::fit()
                    ->height(self::BRAND_HEIGHT)
                    ->width(self::BRAND_WIDTH)
            )
            ->serializeAttributes();

        $url = strval(str_replace(['src=', '"'], ['', ''], $result));

        $fileName = app(MediaService::class)->sanitizeFileName(basename($url));

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$fileName;

        file_put_contents($path, file_get_contents($url));

        return fopen($path, 'r');
    }

    public function uploadBusinessLogo(mixed $resource)
    {
        $stripe = $this->getStripeClient();

        return $stripe->files->create([
            'purpose' => 'business_logo',
            'file' => $resource,
        ]);
    }

    public function updateAccountBrandingBasedOnPlatform(
        string $stripeAccountId,
        bool $isReplacingLogo = true,
        bool $isReplacingColors = true
    ): ?Account {

        $branding = [];

        if ($isReplacingLogo) {

            $logoMedia = app(SettingService::class)->getLogoMedia();

            if ($logoMedia) {

                $resource = $this->getLogoFileFromCloud($logoMedia);

                if ($resource) {

                    $file = $this->uploadBusinessLogo($resource);

                    $branding['logo'] = $file ? $file->id : null;
                }
            }
        }

        if ($isReplacingColors) {
            $stripeSettingService = app(StripeSettingService::class);

            $branding['primary_color'] = $stripeSettingService->primaryColor()
                ?? $stripeSettingService->defaultPrimaryColor();

            $branding['secondary_color'] = $stripeSettingService->secondaryColor()
                ?? $stripeSettingService->defaultSecondaryColor();
        }

        if (!empty($branding)) {
            return $this->updateAccount($stripeAccountId, [
                'settings' => [
                    'branding' => $branding
                ],
            ]);
        }

        return null;
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
        $oneMonthSeconds = 60 * 60 * 24 * 30;

        return app(SettingCache::class)->rememberWithTime('stripe_country_options', function () {
            $stripeSettingService = app(StripeSettingService::class);
            $countrySpecs = [];

            $countrySpecsSetting = $stripeSettingService->getCountrySpecs();

            if (
                is_null($countrySpecsSetting)
                || empty($countrySpecsSetting->value)
                || $countrySpecsSetting->updated_at->lt(now()->subYear())
            ) {
                $response = $this->getStripeClient()->countrySpecs->all(['limit' => 100]);

                $stripeSettingService->saveCountrySpecs($response->data);

                $countrySpecs = $response->data;
            } else {
                $countrySpecs = json_decode($countrySpecsSetting->value);
            }

            $countrySpecs = collect($countrySpecs);

            return Country::whereIn('alpha2', $countrySpecs->pluck('id'))
                ->get(['alpha2', 'display_name'])
                ->map(function ($country) {
                    return [
                        'id' => $country->alpha2,
                        'value' => $country->display_name,
                    ];
                });
        }, $oneMonthSeconds);
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

    public function isStripeConnectEnabled(User $user): bool
    {
        return (new UserMetaStripe($user))->isEnabled();
    }

    private function getUserIdFromStripeAccount(string $stripeAccount): ?int
    {
        return UserMeta::keyAndValue('stripe_account_id', $stripeAccount)
            ->value('user_id');
    }

    public function webhook($payload, $stripeSignature): Response
    {
        $event = null;

        $endpointSecret = $this->getKeys()['stripe_endpoint_secret'] ?? null;

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
            $this->donationThankYouEmail(
                $event->data->object->customer_details->email,
                $event->data->object->amount_total,
                $event->data->object->currency
            );
        }

        PaymentWebhook::create($webhookData);

        return response('Success', 200);
    }

    private function donationThankYouEmail(string $email, int $amount, string $currency)
    {
        if (! $this->isZeroDecimal($currency)) {
            $amount = $amount / 100;
        }

        Mail::to($email)->queue(new ThankYouCheckoutCompleted(
            $amount,
            strtoupper($currency)
        ));
    }

    public function isStripeKeyExists(): bool
    {
        $keys = $this->getKeys();

        if (empty($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            if ($key === null || $key === '') {
                return false;
            }
        }

        return true;
    }
}
