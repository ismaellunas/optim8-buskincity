<?php

namespace App\Http\Middleware;

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

            $response = (new GoogleRecaptcha(
                    $recaptchaKeys['recaptcha_secret_key'] ?? null)
                )
                ->verify($request->input('g-recaptcha-response'), $request->ip());

            if (!$response->isSuccess()) {
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
