<?php

namespace App\Http\Middleware;

use App\Services\RecaptchaService;
use App\Services\SettingService;
use Closure;
use ReCaptcha\ReCaptcha as GoogleRecaptcha;

class Recaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        $settingService = app(SettingService::class);

        if ($settingService->isRecaptchaKeyExists()) {
            $recaptchaKeys = $settingService->getRecaptchaKeys();
            $secretKey = $recaptchaKeys['recaptcha_secret_key'] ?? null;

            $response = (new GoogleRecaptcha($secretKey))
                ->verify($request->input('g-recaptcha-response'), $request->ip());

            if (!$response->isSuccess()) {
                if (
                    in_array(
                        GoogleRecaptcha::E_MISSING_INPUT_RESPONSE,
                        $response->getErrorCodes()
                    )
                ) {
                    RecaptchaService::siteKeyError();

                    return $next($request);
                }

                if (
                    in_array(
                        'invalid-input-secret',
                        $response->getErrorCodes()
                    )
                ) {
                    RecaptchaService::secretKeyError();

                    return $next($request);
                }

                if (!$request->expectsJson()) {
                    return redirect()
                        ->back()
                        ->with('failed', 'Recaptcha failed. Please try again.');
                }

                return response([
                    'success' => false,
                    'message' => __('Recaptcha failed. Please try again.'),
                ]);
            }
        }

        return $next($request);
    }
}
