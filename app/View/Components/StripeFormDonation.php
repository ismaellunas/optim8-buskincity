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

        $this->currencyOptions = $stripeService->getCurrencyOptions();
        $this->amountOptions = $stripeService->getAmountOptions();
        $this->submitUrl = route('donations.checkout', [$userId]);
        $this->listMinimalPayments = $stripeService->getListMinimalPayments();
    }

    public function render()
    {
        return view('components.stripe-form-donation');
    }
}
