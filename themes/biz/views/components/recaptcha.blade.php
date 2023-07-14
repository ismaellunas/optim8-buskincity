<input id="g-recaptcha-response" type="hidden" name="g-recaptcha-response" value="">

@push('bottom_scripts')
    <script>
        var recaptchaSiteKey = "{{ $recaptchaSiteKey }}";

        var loadScript = function(src) {
            return new Promise((resolve, reject) => {
                let script = document.createElement("script");
                script.src = src;

                script.onload = () => resolve(script);
                script.onerror = () => reject(new Error(`failed to load ${src}`));

                document.head.append(script);
            });
        }

        window.addEventListener('load', function () {
            document.getElementById("{{ $formId }}").addEventListener('submit', function(event) {

                event.preventDefault();

                if (!! recaptchaSiteKey) {

                    let src = 'https://www.google.com/recaptcha/api.js?render=' + recaptchaSiteKey;

                    loadScript(src).then(
                        (script) => {
                            grecaptcha.ready(function() {
                                try {
                                    grecaptcha.execute(recaptchaSiteKey, { action: '{{ $action }}' })
                                        .then((response) => {
                                            document.getElementById('g-recaptcha-response').value = response;

                                            event.target.submit();
                                        });
                                } catch (error) {
                                    event.target.submit();
                                }
                            });
                        },
                        (error) => {
                            event.target.submit();
                        }
                    );
                } else {
                    event.target.submit();
                }
            });
        }, false);
    </script>
@endpush
