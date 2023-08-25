<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Entities\UserMetaStripe;
use App\Services\IPService;
use App\Services\StripeService;
use App\Services\StripeSettingService;

class StripeConnectWidget extends BaseWidget implements WidgetInterface
{
    protected $componentName = 'StripeConnect';
    protected $title = "Connect Payments with Stripe";

    private $isStripeKeyExists = false;
    private $stripeService;
    private $userMetaStripe;

    public function __construct()
    {
        parent::__construct();

        $this->stripeService = new StripeService();

        $this->isStripeKeyExists = app(StripeService::class)->isStripeKeyExists();
    }

    protected function getTitle(): string
    {
        if ($this->hasConnectedAccount()) {
            return __('Manage Payments');
        }

        return parent::getTitle();
    }

    protected function getData(): array
    {
        if (! $this->isStripeKeyExists) {
            return [];
        }

        $hasConnectedAccount = $this->hasConnectedAccount();

        $defaultCountry = app(IPService::class)->getCountryCode(
            app(StripeSettingService::class)->getDefaultCountry()
        );

        return [
            'countryOptions' => $this->stripeService->getCountryOptions(),
            'defaultCountry' => $defaultCountry,
            'hasConnectedAccount' => $hasConnectedAccount,
        ];
    }

    protected function i18n(): array
    {
        return [
            'inconnect' => __(
                'If you would like to receive donations and payments for private gigs through :appName, please apply for payments with Stripe:',
                [
                    'appName' => config('app.name'),
                ]
            ),
            'country' => __('Country'),
            'select_option' => __('Select option'),
            'create_connected_account' => __('Create connected account'),
            'connect' => __(
                'Visit the payment’s dashboard to manage your Stripe Connect account.'
            ),
            'manage_payments' => __('Manage payments'),
            'create_alert' => [
                'title' => __('Please double-check your country!'),
                'text' => __('You will not be able to change your country in the future.'),
                'button' => __('Continue'),
            ],
        ];
    }

    private function hasConnectedAccount(): bool
    {
        return $this->getUserMetaStripe()->hasAccount() ?? false;
    }

    private function getUserMetaStripe(): UserMetaStripe
    {
        if (is_null($this->userMetaStripe)) {
            $this->userMetaStripe = new UserMetaStripe(auth()->user());
        }

        return $this->userMetaStripe;
    }

    public function canBeAccessed(): bool
    {
        return $this->user->can('manageStripeConnectedAccount', $this->user)
            && $this->isStripeKeyExists;
    }
}
