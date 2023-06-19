@inject('settingService', 'App\Services\SettingService')

<div
    id="js-cookie-consent-dialog"
    class="js-cookie-consent columns is-mobile m-0"
    style="position: fixed; z-index: 9999; bottom: 0; width: 100%;"
>
    <div class="column is-3-widescreen is-offset-9-widescreen is-4-desktop is-offset-8-desktop is-12-tablet is-12-mobile p-0">
        <div class="notification is-primary">
            {!! $settingService->getCookieConsentMessage() !!}

            <div class="buttons">
                <button
                    id="js-cookie-consent-decline"
                    class="button is-white is-outlined mt-4"
                >
                    {{ __('Decline') }}
                </button>

                <button
                    id="js-cookie-consent-agree"
                    class="button is-white mt-4"
                >
                    {{ __('Allow cookies') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div
    id="js-cookie-consent-dialog-decline"
    class="js-cookie-consent columns is-mobile m-0 is-hidden"
    style="position: fixed; z-index: 9999; bottom: 0; width: 100%;"
>
    <div class="column is-3-widescreen is-offset-9-widescreen is-4-desktop is-offset-8-desktop is-12-tablet is-12-mobile p-0">
        <div class="notification is-primary">
            {!! $settingService->getCookieConsentMessageDecline() !!}

            <div class="buttons">
                <button
                    id="js-cookie-consent-close"
                    class="button is-white is-outlined mt-4"
                >
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
</div>
