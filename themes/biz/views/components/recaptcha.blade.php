<input id="g-recaptcha-response" type="hidden" name="g-recaptcha-response" value="">

@push('bottom_scripts')
    <script>
        window.addEventListener('load', function () {
            document.getElementById("{{ $formId }}").addEventListener('submit', function(event) {
                event.preventDefault();

                const recaptchaSiteKey = "{{ $recaptchaSiteKey }}";

                if (!! recaptchaSiteKey) {
                    let recaptchaScript = document.createElement('script')

                    recaptchaScript.setAttribute(
                        'src',
                        'https://www.google.com/recaptcha/api.js?render=' + recaptchaSiteKey
                    );
                    document.head.appendChild(recaptchaScript);
                }

                setTimeout(() => {
                    if (typeof grecaptcha !== 'undefined') {
                        grecaptcha.ready(function() {
                            try {
                                grecaptcha.execute('{{ $recaptchaSiteKey }}', { action: '{{ $action }}' })
                                    .then((response) => {
                                        document.getElementById('g-recaptcha-response').value = response;

                                        event.target.submit();
                                    });
                            } catch (error) {
                                event.target.submit();
                            }
                        });
                    } else {
                        event.target.submit();
                    }
                }, 200);
            });
        }, false);
    </script>
@endpush
