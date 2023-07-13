<?php

namespace App\Http\Controllers;

use App\Services\PageService;
use App\Http\Requests\CookieConsentSettingRequest;
use App\Models\Setting;
use App\Traits\FlashNotifiable;
use Inertia\Inertia;

class CookieConsentController extends Controller
{
    use FlashNotifiable;

    private $title = 'Cookie Consent';
    private $baseRouteName = 'admin.settings.cookie-consent';
    private $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    public function edit()
    {
        return Inertia::render('CookieConsent', [
            'title' => __($this->title),
            'baseRouteName' => $this->baseRouteName,
            'settings' => $this->getSettings(),
            'pageOptions' => $this->pageService->getPageOptions(__('None')),
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

        Setting::updateOrCreate(
            [
                'key' => 'cookie_consent_redirect_decline_page_id',
                'group' => 'cookie_consent'
            ],
            ['value' => $inputs['redirect_decline_page_id']]
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
            'cookie_consent_redirect_decline_page_id',
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
            'redirection' => __('Redirection'),
            'redirect_after_decline' => __('Redirect page ID'),
            'redirect_after_decline_note' => __('Redirect to the page after clicking the decline button.'),
        ];
    }
}
