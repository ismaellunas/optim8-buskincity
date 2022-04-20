<?php

namespace App\Http\Controllers;

use App\Http\Responses\VerifyEmailResponse;
use Illuminate\Auth\Events\Verified;
use Laravel\Fortify\Http\Controllers\VerifyEmailController as FortifyVerifyEmailController;
use Laravel\Fortify\Http\Requests\VerifyEmailRequest;

class VerifyEmailController extends FortifyVerifyEmailController
{
    public function __invoke(VerifyEmailRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return app(VerifyEmailResponse::class);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return app(VerifyEmailResponse::class);
    }
}
