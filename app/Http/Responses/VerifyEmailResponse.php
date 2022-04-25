<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\VerifyEmailResponse as FortifyVerifyEmailResponse;

class VerifyEmailResponse extends FortifyVerifyEmailResponse implements VerifyEmailResponseContract
{
    /** @override */
    public function toResponse($request)
    {
        if (
            !$request->wantsJson()
            && $request->routeIs('admin.*')
        ) {
            return redirect()->intended(
                Fortify::redirects('admin_dashboard').'?verified=1'
            );
        }

        return parent::toResponse($request);
    }
}
