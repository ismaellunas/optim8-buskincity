<?php

namespace App\Http\Controllers;

use App\Http\Requests\CookieConsentSettingRequest;
use App\Models\Setting;
use App\Traits\FlashNotifiable;
use Inertia\Inertia;

class CookieConsentController extends Controller
{
    use FlashNotifiable;

    private $title = 'Cookie Consent';
    private $baseRouteName = 'admin.settings.cookie-consent';

    public function edit()
    {
        return Inertia::render('CookieConsent', [
            'title' => __($this->title),
            'baseRouteName' => $this->baseRouteName,
            'settings' => $this->getSettings(),
            'i18n' => $this->translations(),
        ]);
    }

    public function update(CookieConsentSettingRequest $request)
    {
        $inputs = $request->validated();

        Setting::updateOrCreate(
            [
                'key' => 'cookie_consent_is_enabled',
                'group' => 'cookie_consent'
            ],
            ['value' => $inputs['is_enabled']]
        );

        Setting::updateOrCreate(
            [
                'key' => 'cookie_consent_message',
                'group' => 'cookie_consent'
            ],
            ['value' => $inputs['message']]
        );

        Setting::updateOrCreate(
            [
                'key' => 'cookie_consent_message_decline',
                'group' => 'cookie_consent'
            ],
            ['value' => $inputs['message_decline']]
        );

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __($this->title),
        ]);

        return back();
    }

    private function getSettings(): array
    {
        $settings = Setting::whereIn('key', [
            'cookie_consent_is_enabled',
            'cookie_consent_message',
            'cookie_consent_message_decline',
        ])->get()->pluck('value', 'key')->all();

        $settings['cookie_consent_is_enabled'] = boolval($settings['cookie_consent_is_enabled'] ?? false);

        return $settings;
    }

    private function translations(): array
    {
        return [
            'save' => __('Save'),
            'settings' => __('Settings'),
            'is_enabled' => __('Is enabled?'),
            'enabled' => __('Enabled'),
            'disabled' => __('Disabled'),
            'message_templates' => __('Message templates'),
            'message' => __('Message'),
            'message_decline' => __('Message decline'),
        ];
    }
}
