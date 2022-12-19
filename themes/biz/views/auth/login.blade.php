@inject('loginService', 'App\Services\LoginService')

<x-layouts.auth>
    <div class="column is-three-fifths has-text-left">
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <a onclick="backOrOpenSocialMediaForm()">
                        <span class="icon"><i class="fas fa-arrow-left"></i></span>
                        <span>Back</span>
                    </a>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <span class=mr-3>
                        Don't have an account?
                    </span>
                    <a href="{{ route('register') }}">
                        Sign Up
                    </a>
                </div>
            </div>
        </div>
        <section class="section">
            @if (session('message'))
                <div class="columns">
                    <div class="column is-9 is-offset-1">
                        <div class="notification is-info">
                            {{ session('message') }}
                        </div>
                    </div>
                </div>
            @endif

            @if (session('failed'))
                <div class="columns">
                    <div class="column is-9 is-offset-1">
                        <div class="notification is-danger">
                            {{ session('failed') }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="columns" id="socialMediaForm">
                <div class="column is-9 is-offset-1">
                    <h1 class="title">Log In</h1>
                    <h2 class="subtitle">
                        <span>Please login to continue</span>
                    </h2>
                    <div class="has-text-centered">
                        @php
                            $availableSocialiteDrivers = $loginService->getAvailableSocialiteDrivers();
                        @endphp

                        @if (!empty($availableSocialiteDrivers))
                            @foreach ($availableSocialiteDrivers as $driver)
                                <a href="{{ route('oauth.redirect', $driver) }}" class="box">
                                    <i class="fab fa-{{ $driver }}"></i> Continue with <b>{{ Str::title($driver) }}</b>
                                </a>
                            @endforeach

                            <div class="h-line-wrapper">
                                <span class="h-line-words">or</span>
                            </div>
                        @endif

                        <a class="box" onclick="showForm()">
                            <i class="fas fa-envelope"></i> Continue with <b>Email</b>
                        </a>
                    </div>
                </div>
            </div>
            <div class="columns is-hidden" id="formFields">
                <div class="column is-9 is-offset-1">
                    <h1 class="title">
                        Welcome Back
                    </h1>
                    <h2 class="subtitle">
                        <span>Lorem ipsum dolor sit amet.</span>
                    </h2>

                    @if ($errors->any())
                        <div class="notification is-danger mb-4">
                            <button
                                class="delete"
                                type="button"
                                onclick="removeErrorMessage(this)"
                            ></button>

                            <ul class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="has-text-left">
                        <form action="{{ route('login') }}" method="post" onsubmit="setLoader()">
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

                            <div class="field">
                                <label class="label">Password</label>
                                <div class="control">
                                    <div class="field has-addons mb-0">
                                        <div class="control is-expanded">
                                            <input type="password" name="password" id="input-password" class="input" placeholder="Enter your password" required>
                                        </div>
                                        <div class="control icon-password" onclick="showHidePassword(this)" data-target="input-password">
                                            <button type="button" class="button" tabindex="-1">
                                                <span class="icon">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </button>
                                            <button type="button" class="button is-hidden" tabindex="-1">
                                                <span class="icon">
                                                    <i class="fas fa-eye-slash"></i>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @error('password')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field columns">
                                <div class="column has-text-left">
                                    <label class="checkbox">
                                        <input type="checkbox" name="remember">
                                        <span class="pl-1">Remember me</span>
                                    </label>
                                </div>
                                <div class="column has-text-right">
                                    <a href="{{ route('password.request') }}">
                                        Forgot your password?
                                    </a>
                                </div>
                            </div>

                            @if (!empty($recaptchaSiteKey))
                                <div
                                    class="g-recaptcha"
                                    data-sitekey="{{ $recaptchaSiteKey }}"
                                    data-size="invisible"
                                ></div>
                            @endif

                            <button type="submit" class="button is-block is-info is-fullwidth">
                                Log In <i class="fas fa-sign-in-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('bottom_scripts')
        @if ($errors->any())
            <script>
                showForm();
            </script>
        @endif

        @if (!empty($recaptchaSiteKey))
            <script>
                var onloadCallback = function() {
                    grecaptcha.execute();
                };
            </script>
            <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback" defer></script>
        @endif
        <script>
            function removeErrorMessage(element) { element.parentElement.remove() };
        </script>
    @endpush
</x-layouts.auth>