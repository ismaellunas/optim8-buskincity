<?php

namespace App\Entities\Widgets;

use App\Entities\UserMetaStripe;
use App\Contracts\WidgetInterface;
use App\Services\{
    IPService,
    StripeService,
    StripeSettingService,
};

class StripeConnectWidget implements WidgetInterface
{
    protected $data = [];
    protected $title = "Connect Payments with Stripe";
    protected $componentName = 'StripeConnect';
    protected $user;

    private $stripeService;
    private $userMetaStripe;

    public function __construct()
    {
        $this->stripeService = new StripeService();

        $this->user = auth()->user();
        $this->data = $this->getWidgetData();
    }

    public function data(): array
    {
        return [
            'title' => $this->getTitle(),
            'componentName' => $this->componentName,
            'data' => $this->data,
        ];
    }

    private function getTitle(): string
    {
        if ($this->hasConnectedAccount()) {
            return 'Stripe Connect';
        }
        return $this->title;
    }

    private function getWidgetData(): array
    {
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

    private function hasConnectedAccount(): bool
    {
        return $this->getUserMetaStripe()->hasAccount() ?? false;
    }

    private function getUserMetaStripe()
    {
        if (is_null($this->userMetaStripe)) {
            $this->userMetaStripe = new UserMetaStripe(auth()->user());
        }

        return $this->userMetaStripe;
    }

    public function canBeAccessed(): bool
    {
        return $this->user->can('manageStripeConnectedAccount', $this->user);
    }
}
