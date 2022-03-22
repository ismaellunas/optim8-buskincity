<?php

namespace App\View\Components;

use App\Services\StripeService;
use App\Services\StripeSettingService;
use Illuminate\View\Component;

class StripeFormDonation extends Component
{
    public $amountOptions;
    public $currencyOptions;
    public $submitUrl;
    public $listMinimalPayments;

    public function __construct($userId)
    {
        $stripeService = app(StripeService::class);
        $stripeSettingService = app(StripeSettingService::class);

        $this->amountOptions = $stripeSettingService->getAmountOptions();
        $this->currencyOptions = $stripeSettingService->getAvailableCurrencyOptions();
        $this->listMinimalPayments = $stripeService->getListMinimalPayments();
        $this->submitUrl = route('donations.checkout', [$userId]);
    }

    public function render()
    {
        return view('components.stripe-form-donation');
    }
}
