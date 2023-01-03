@if (!empty($recaptchaSiteKey))
    <div
        class="g-recaptcha"
        data-sitekey="{{ $recaptchaSiteKey }}"
        data-size="invisible"
        data-error-callback="recaptchaError"
    ></div>
@endif

<span
    id="recaptcha-error-message"
    class="help has-text-danger is-hidden"
>
    Please check the reCAPTCHA!
</span>

@push('scripts')
    <script>
        function recaptchaError() {
            document.getElementById('recaptcha-error-message').classList.remove('is-hidden');
        }
    </script>
@endpush

@push('bottom_scripts')
    @if (!empty($recaptchaSiteKey))
        <script>
            var onloadCallback = function() {
                grecaptcha.execute();
            };
        </script>

        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback" defer></script>
    @endif
@endpush