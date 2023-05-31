<input id="g-recaptcha-response" type="hidden" name="g-recaptcha-response" value="">

@if (! empty($recaptchaSiteKey))
    <span
        id="recaptcha-error-message"
        class="help has-text-danger is-hidden"
    >
        {{ __('Please check the reCAPTCHA!') }}
    </span>
@endif

@push('bottom_scripts')
    @if (! empty($recaptchaSiteKey))
        <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
    @endif

    <script>
        window.addEventListener('load', function () {
            document.getElementById("{{ $formId }}").addEventListener('submit', function(event) {
                event.preventDefault();

                if (typeof grecaptcha !== 'undefined') {
                    grecaptcha.ready(function() {
                        try {
                            grecaptcha.execute('{{ $recaptchaSiteKey }}', { action: '{{ $action }}' })
                                .then((response) => {
                                    document.getElementById('g-recaptcha-response').value = response;

                                    event.target.submit();
                                });
                        } catch (error) {
                            document.getElementById('recaptcha-error-message').classList.remove('is-hidden');

                            event.target.submit();
                        }
                    });
                } else {
                    event.target.submit();
                }
            });
        }, false);
    </script>
@endpush
