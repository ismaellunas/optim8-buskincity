<?php

namespace App\View\Components;

use App\Services\StripeService;
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

        $this->amountOptions = $stripeService->getAmountOptions();
        $this->currencyOptions = $stripeService->getAvailableCurrencyOptions();
        $this->listMinimalPayments = $stripeService->getListMinimalPayments();
        $this->submitUrl = route('donations.checkout', [$userId]);
    }

    public function render()
    {
        return view('components.stripe-form-donation');
    }
}
