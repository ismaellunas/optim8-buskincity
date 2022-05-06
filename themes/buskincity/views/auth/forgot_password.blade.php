<x-layouts.auth>
    <x-slot name="title">
        {{ __('Forgot Password') }} | {{ config('app.name') }}
    </x-slot>

    <div class="columns">
        <div class="column is-5 is-hidden-mobile">
            <img src="{{ url('/themes/buskincity/images/login.jpg') }}" alt="BuskinCity buskers performing on the street" class="is-radius">
        </div>
        <div class="is-flex is-flex-direction-column column is-7">
            <nav class="level is-mobile">
                <!-- Left side -->
                <div class="level-left">
                    <a href="{{ route('login') }}"  class="button is-white">
                        <span class="icon is-small">
                            <i class="fa-regular fa-arrow-left"></i>
                        </span>
                        <span class="has-text-weight-bold">{{ __('Back') }}</span>
                    </a>
                </div>

                <!-- Right side -->
                <div class="level-right">
                    <span>{{ __('Don’t have an account?') }}</span>
                    <a href="{{ route('register') }}" class="button is-primary is-outlined is-responsive ml-4">
                        <span class="has-text-weight-bold">{{ __('Sign Up') }}</span>
                    </a>
                </div>
            </nav>

            <div class="columns is-vcentered is-flex-grow-1">
                @if (session()->get('status_key') == 'passwords.sent')
                    <div class="column is-8 is-offset-2 has-text-centered">
                        <i class="fa-light fa-paper-plane is-size-1 has-text-primary mb-5"></i>
                        <h1 class="title is-2 mb-4">{{ __('Instructions Sent!') }}</h1>
                        <p>{{ __('Please check your email with instructions to reset your password.') }}</p>
                    </div>
                @else
                    <div class="column is-8 is-offset-2">
                        <h1 class="title is-2 mb-4">{{ __('Forgot Password') }}</h1>
                        <p>{{ __('We will send you an email with instructions to reset your password.') }}</p>

                        @if (session()->has('failed'))
                            <div class="notification is-danger mt-4">
                                {{ session()->get('failed') }}
                            </div>
                        @endif

                        @if (session()->has('status'))
                            <div class="notification is-info mt-4">
                                {{ session()->get('status') }}
                            </div>
                        @endif

                        <form action="{{ route('password.email') }}" onsubmit="setLoader()" method="post" class="mt-6">
                            @csrf
                            <div class="field mb-5">
                                <label class="label">Email</label>
                                <div class="control">
                                    <input type="email" name="email" value="{{ old('email') }}" class="input" placeholder="{{ __('Enter your email') }}" required>
                                </div>
                                @error('email')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div
                                class="g-recaptcha"
                                data-sitekey="{{ config('constants.recaptcha_site_key') }}"
                                data-size="invisible"
                            ></div>

                            <button type="submit" class="button is-medium is-primary is-fullwidth">
                                <span class="has-text-weight-bold">{{ __('Send Reset Link')}}</span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('bottom_scripts')
        <script>
            var onloadCallback = function() {
                grecaptcha.execute();
            };
        </script>
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback" defer></script>
    @endpush
</x-layouts.auth>