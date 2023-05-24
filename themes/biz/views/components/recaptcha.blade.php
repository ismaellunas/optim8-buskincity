<span
    id="recaptcha-error-message"
    class="help has-text-danger is-hidden"
>
    Please check the reCAPTCHA!
</span>

@if (!empty($recaptchaSiteKey))
    <div
        class="g-recaptcha"
        data-sitekey="{{ $recaptchaSiteKey }}"
        data-size="invisible"
        data-error-callback="recaptchaError"
    ></div>

    @push('bottom_scripts')
        <script>
            function recaptchaError() {
                document.getElementById('recaptcha-error-message').classList.remove('is-hidden');
            };

            var onloadCallback = function() {
                grecaptcha.execute();
            };
        </script>

        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback" async defer></script>
    @endpush
@endif
