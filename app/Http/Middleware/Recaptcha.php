<?php

namespace App\Http\Middleware;

use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
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
    public function handle(Request $request, Closure $next)
    {
        $settingService = app(SettingService::class);

        if ($settingService->isRecaptchaKeyExists()) {
            $recaptchaKeys = $settingService->getRecaptchaKeys();
            $secretKey = $recaptchaKeys['recaptcha_secret_key'] ?? null;

            $response = (new GoogleRecaptcha($secretKey))
                ->verify($request->input('g-recaptcha-response'), $request->ip());

            if (
                ! $response->isSuccess()
                || (
                    $response->isSuccess()
                    && $response->getScore() < config('constants.settings.recaptcha.minimal_score')
                )
            ) {
                if (!$request->expectsJson()) {
                    return redirect()
                        ->back()
                        ->with('failed', __('Recaptcha failed. Please try again.'));
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
