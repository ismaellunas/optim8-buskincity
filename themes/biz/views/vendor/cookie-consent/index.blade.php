@inject('settingService', 'App\Services\SettingService')

@if($settingService->isCookieConsentEnabled() && ! $alreadyConsentedWithCookies)

    @include('cookie-consent::dialogContents')

    <script>

        window.laravelCookieConsent = (function () {

            const COOKIE_VALUE = 1;
            const COOKIE_DOMAIN = '{{ config('session.domain') ?? request()->getHost() }}';
            const redirectDeclineUrl = '{{ $settingService->getCookieConsentRedirectDeclineUrl() }}';

            function consentWithCookies() {
                setCookie('{{ $cookieConsentConfig['cookie_name'] }}', COOKIE_VALUE, {{ $cookieConsentConfig['cookie_lifetime'] }});
                hideCookieDialog();
            }

            function cookieExists(name) {
                return (document.cookie.split('; ').indexOf(name + '=' + COOKIE_VALUE) !== -1);
            }

            function hideCookieDialog() {
                const dialog = document.getElementById('js-cookie-consent-dialog');

                dialog.classList.add('is-hidden');
            }

            function handleCookieConsentDecline() {
                if (redirectDeclineUrl && redirectDeclineUrl !== '') {
                    window.location.href = redirectDeclineUrl;
                } else {
                    showCookieDialogDecline();
                }
            }

            function showCookieDialogDecline() {
                const dialog = document.getElementById('js-cookie-consent-dialog-decline');

                dialog.classList.remove('is-hidden');

                hideCookieDialog();
            }

            function hideCookieDialogDecline() {
                const dialog = document.getElementById('js-cookie-consent-dialog-decline');

                dialog.classList.add('is-hidden');
            }

            function setCookie(name, value, expirationInDays) {
                const date = new Date();
                date.setTime(date.getTime() + (expirationInDays * 24 * 60 * 60 * 1000));
                document.cookie = name + '=' + value
                    + ';expires=' + date.toUTCString()
                    + ';domain=' + COOKIE_DOMAIN
                    + ';path=/{{ config('session.secure') ? ';secure' : null }}'
                    + '{{ config('session.same_site') ? ';samesite='.config('session.same_site') : null }}';
            }

            if (cookieExists('{{ $cookieConsentConfig['cookie_name'] }}')) {
                hideCookieDialog();
            }

            // Custom Script
            document.getElementById('js-cookie-consent-agree')
                .addEventListener('click', consentWithCookies);

            document.getElementById('js-cookie-consent-decline')
                .addEventListener('click', handleCookieConsentDecline);

            document.getElementById('js-cookie-consent-close')
                .addEventListener('click', hideCookieDialogDecline);

            return {
                consentWithCookies: consentWithCookies,
                hideCookieDialog: hideCookieDialog
            };
        })();
    </script>

@endif
