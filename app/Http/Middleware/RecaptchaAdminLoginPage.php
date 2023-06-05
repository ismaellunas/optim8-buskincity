<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use ReCaptcha\ReCaptcha as GoogleRecaptcha;

class RecaptchaAdminLoginPage extends Recaptcha
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->settingService->isRecaptchaKeyExists()) {
            $recaptchaKeys = $this->settingService->getRecaptchaKeys();
            $secretKey = $recaptchaKeys['recaptcha_secret_key'] ?? null;

            $response = (new GoogleRecaptcha($secretKey))
                ->verify($request->input('g-recaptcha-response'), $request->ip());
            if (! $response->isSuccess()) {
                if (
                    in_array(
                        GoogleRecaptcha::E_MISSING_INPUT_RESPONSE,
                        $response->getErrorCodes()
                    )
                    || in_array(
                        'invalid-input-secret',
                        $response->getErrorCodes()
                    )
                ) {

                    return $next($request);

                }

                return $this->failRequestAction($request);
            }

            if (
                $response->isSuccess()
                && $response->getScore() < $this->getRecaptchaScore()
            ) {

                return $this->failRequestAction($request);

            }
        }

        return $next($request);
    }
}
