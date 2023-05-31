<?php

namespace App\Http\Middleware;

use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
use ReCaptcha\ReCaptcha as GoogleRecaptcha;

class Recaptcha
{
    protected $settingService;

    public function __construct()
    {
        $this->settingService = app(SettingService::class);
    }

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

    protected function getRecaptchaScore(): float
    {
        return $this->settingService->getRecaptchaScore();
    }

    private function failRequestAction(Request $request)
    {
        if (! $request->expectsJson()) {
            return redirect()
                ->back()
                ->withErrors([
                    'recaptcha' => __('The process could not be completed due to a connection problem. Please try again.'),
                ]);
        }

        return response([
            'success' => false,
            'message' => __('The process could not be completed due to a connection problem. Please try again.'),
        ]);
    }
}
