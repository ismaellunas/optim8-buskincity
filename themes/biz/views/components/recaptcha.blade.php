@if (!empty($recaptchaSiteKey))
    <input id="g-recaptcha-response" type="hidden" name="g-recaptcha-response" value="">

    <span
        id="recaptcha-error-message"
        class="help has-text-danger is-hidden"
    >
        {{ __('Please check the reCAPTCHA!') }}
    </span>

    @push('bottom_scripts')
        <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
        <script>
            grecaptcha.ready(function() {
                try {
                    grecaptcha.execute('{{ $recaptchaSiteKey }}', { action: '{{ $tag }}' })
                        .then((response) => {
                            document.getElementById('g-recaptcha-response').value = response;
                        });
                } catch (error) {
                    document.getElementById('recaptcha-error-message').classList.remove('is-hidden');
                }
            });
        </script>
    @endpush
@endif