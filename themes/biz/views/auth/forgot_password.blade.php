<x-layouts.auth>
    <div class="column is-three-fifths has-text-left">
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <a href="{{ route('login') }}">
                        <span class="icon"><i class="fas fa-arrow-left"></i></span>
                        <span>Back</span>
                    </a>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="columns" id="formFields">
                <div class="column is-9 is-offset-1">
                    <div class="mb-4">
                        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
                    </div>

                    @if (session()->has('failed'))
                        <div class="notification is-danger">
                            {{ session()->get('failed') }}
                        </div>
                    @endif

                    @if (session()->has('status'))
                        <div class="notification is-primary">
                            {{ session()->get('status') }}
                        </div>
                    @endif

                    <form method="post" action="{{ route('password.email') }}" onsubmit="setLoader()">
                        <div class="mb-4">
                            @csrf

                            <div class="field">
                                <label class="label">Email</label>
                                <div class="control">
                                    <input type="email" name="email" value="{{ old('email') }}" class="input" placeholder="Enter your email" required>
                                </div>
                                @error('email')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

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

                        <div class="mt-4">
                            <button type="submit" class="button is-info">
                                Email Password Reset Link
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

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
</x-layouts.auth>