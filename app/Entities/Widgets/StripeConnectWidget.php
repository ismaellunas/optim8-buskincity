<?php

namespace App\Entities\Widgets;

use App\Contracts\WidgetInterface;
use App\Entities\UserMetaStripe;
use App\Services\IPService;
use App\Services\StripeService;
use App\Services\StripeSettingService;

class StripeConnectWidget extends BaseWidget implements WidgetInterface
{
    protected $component = 'StripeConnect';

    private $isStripeKeyExists = false;
    private $stripeService;
    private $userMetaStripe;

    public function __construct(array $storedSetting)
    {
        parent::__construct($storedSetting);

        $this->stripeService = app(StripeService::class);

        $this->isStripeKeyExists = $this->stripeService->isStripeKeyExists();
    }

    protected function getTitle(): string
    {
        if ($this->hasConnectedAccount()) {
            return __(
                $this->storedSetting['i18n']['manage_payments']
            );
        }

        return parent::getTitle();
    }

    protected function getData(): array
    {
        if (! $this->isStripeKeyExists) {
            return parent::getData();
        }

        $hasConnectedAccount = $this->hasConnectedAccount();

        $defaultCountry = app(IPService::class)->getCountryCode(
            app(StripeSettingService::class)->getDefaultCountry()
        );

        return [
            ...parent::getData(),
            ...[
                'countryOptions' => $this->stripeService->getCountryOptions(),
                'defaultCountry' => $defaultCountry,
                'hasConnectedAccount' => $hasConnectedAccount,
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
        return parent::canBeAccessed()
            && (
                $this->user->can('manageStripeConnectedAccount', $this->user)
                && $this->isStripeKeyExists
            );
    }
}
