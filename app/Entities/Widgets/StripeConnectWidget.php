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
            'title' => $this->title,
            'componentName' => $this->componentName,
            'data' => $this->data,
        ];
    }

    private function getWidgetData(): array
    {
        $hasConnectedAccount = $this->getUserMetaStripe()->hasAccount();
        $defaultCountry = app(IPService::class)->getCountryCode(
            app(StripeSettingService::class)->getDefaultCountry()
        );

        return [
            'countryOptions' => $this->stripeService->getCountryOptions(),
            'defaultCountry' => $defaultCountry,
            'hasConnectedAccount' => $hasConnectedAccount,
        ];
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
        return $this->user->hasRole('Performer');
    }
}
