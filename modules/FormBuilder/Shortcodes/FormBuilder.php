<?php

namespace Modules\FormBuilder\Shortcodes;

use App\Services\SettingService;
use Illuminate\Support\Str;

class FormBuilder
{
    public function register($shortcode)
    {
        $formAttribute = null;
        $attributes = $shortcode->toArray();

        foreach ($attributes as $attribute) {
            if (Str::startsWith($attribute, 'form-id')) {
                $formAttribute = $attribute;
            }
        }

        $recaptchaKeys = app(SettingService::class)->getRecaptchaKeys();
        $recaptchaSiteKey = $recaptchaKeys['recaptcha_site_key'] ?? null;

        if ($recaptchaSiteKey) {
            $formAttribute .= __(' recaptcha-site-key=":siteKey"', [
                "siteKey" => $recaptchaSiteKey
            ]);
        }

        return sprintf(
            '<form-builder %s></form-builder>',
            $formAttribute
        );
    }
}
