<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\Request;

class WebhookStripeController extends Controller
{
    private $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function __invoke(Request $request)
    {
        return $this->stripeService->webhook(
            $request->getContent(),
            $request->server('HTTP_STRIPE_SIGNATURE')
        );
    }
}
