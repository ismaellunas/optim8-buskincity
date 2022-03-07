<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\DonationRequest;
use App\Models\User;
use App\Services\StripeService;

class DonationController extends Controller
{
    private $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function checkout(DonationRequest $request, User $user)
    {
        $session = $this->stripeService->checkout(
            $user,
            (float) $request->amount,
            $request->currency
        );

        return redirect()->away($session->url);
    }

    public function success(User $user)
    {
        return view('donation-success', ['user' => $user]);
    }

    public function cancel(User $user)
    {
        return view('donation-cancel', ['user' => $user]);
    }
}
