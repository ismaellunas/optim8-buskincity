<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\Handler;
use App\Http\Controllers\Controller;
use App\Http\Requests\DonationRequest;
use App\Models\User;
use App\Services\ErrorLogService;
use App\Services\StripeService;

class DonationController extends Controller
{
    private $stripeService;
    private $errorLogService;

    public function __construct(
        StripeService $stripeService,
        ErrorLogService $errorLogService,
    ) {
        $this->stripeService = $stripeService;
        $this->errorLogService = $errorLogService;
    }

    public function checkout(DonationRequest $request, User $user)
    {
        try {
            $session = $this->stripeService->checkout(
                $user,
                (float) $request->amount,
                $request->currency
            );

            return redirect()->away($session->url);
        } catch (\Throwable $th) {
            $this->errorLogService->report($th);

            return redirect()
                ->back()
                ->with('error', __("We're sorry, but there was a problem processing your donation. Please contact the web administrator for assistance."));
        }
    }

    public function success(User $user)
    {
        return view('donation-success', [
            'user' => $user,
        ]);
    }
}
