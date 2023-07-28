<x-layouts.auth>
    <x-slot name="title">
        {{ __('Login | :appName', ['appName' => config('app.name')]) }}
    </x-slot>

    <div class="columns is-mobile">
        <div class="column is-5-desktop is-6-tablet is-hidden-mobile">
            <x-image
                src="{{ url('/themes/buskincity/images/login.jpg') }}"
                alt="BuskinCity buskers performing on the street"
                class="is-radius"
                width="470"
                height="600"
                is-lazyload
            />
        </div>

        <div class="is-flex is-flex-direction-column column is-7-desktop is-6-tablet is-12-mobile">
            <nav class="level is-mobile">
                <!-- Left side -->
                <div class="level-left">
                    <a class="button is-white" onclick="backOrOpenSocialMediaForm()">
                        <x-icon icon="fa-arrow-left" is-small />
                        <span class="has-text-weight-bold">
                            {{ __('Back') }}
                        </span>
                    </a>
                </div>

                <!-- Right side -->
                <div class="level-right">
                    <span>
                        {{ __("Don’t have an account?") }}
                    </span>
                    <a href="{{ route('register') }}" class="button is-primary is-outlined is-responsive ml-4">
                        <span class="has-text-weight-bold">
                            {{ __('Sign Up') }}
                        </span>
                    </a>
                </div>
            </nav>

            @if (session('message'))
                <div class="columns is-mobile is-vcentered is-flex-grow-1 error-message">
                    <div class="column is-8-desktop is-offset-2-desktop is-10-tablet is-offset-1-tablet is-12-mobile">
                        <div class="notification is-info">
                            <button
                                class="delete"
                                type="button"
                                onclick="removeErrorMessage()"
                            ></button>

                            {{ session('message') }}
                        </div>
                    </div>
                </div>
            @endif

            @if (session('failed'))
                <div class="columns is-mobile is-vcentered is-flex-grow-1 error-message">
                    <div class="column is-8-desktop is-offset-2-desktop is-10-tablet is-offset-1-tablet is-12-mobile">
                        <div class="notification is-danger">
                            <button
                                class="delete"
                                type="button"
                                onclick="removeErrorMessage()"
                            ></button>

                            {{ session('failed') }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="columns is-mobile is-vcentered is-flex-grow-1">
                <div id="socialMediaForm" class="column is-8-desktop is-offset-2-desktop is-10-tablet is-offset-1-tablet is-12-mobile">
                    <h1 class="title is-2 mb-4">
                        {{ __('Log In') }}
                    </h1>
                    <p>
                        {{ __('Please log in to continue.') }}
                    </p>

                    @if (!empty($availableSocialiteDrivers))
                        @foreach ($availableSocialiteDrivers as $driver)
                            <a href="{{ route('oauth.redirect', $driver) }}" @class([
                                "button is-medium is-light is-fullwidth",
                                "mt-6" => $loop->first,
                                "mt-4" => !$loop->first,
                            ])>
                                <x-icon icon="fa-brands fa-{{ $driver }}" is-small/>
                                <span>
                                    {!! __('Continue with :driver', ['driver' => '<span class="has-text-weight-bold">'.Str::title($driver).'</span>']) !!}
                                </span>
                            </a>
                        @endforeach

                        <div class="is-divider mt-6 mb-6 ml-5 mr-6" data-content="OR"></div>
                    @endif

                    <a class="button is-medium is-light is-fullwidth mt-4" onclick="showForm()">
                        <x-icon icon="fa-envelope is-small" />
                        <span>
                            {!! __('Continue with :driver', ['driver' => '<span class="has-text-weight-bold">Email</span>']) !!}
                        </span>
                    </a>
                </div>

                <div id="formFields" class="column is-8-desktop is-offset-2-desktop is-10-tablet is-offset-1-tablet is-12-mobile is-hidden">
                    <h1 class="title is-2 mb-4">
                        {{ __('Welcome Back') }}
                    </h1>

                    <p>
                        {{ __('Fill in your email and password to login.') }}
                    </p>

                    @if ($errors->any())
                        <div class="notification is-danger mt-4 error-message">
                            <button
                                class="delete"
                                type="button"
                                onclick="removeErrorMessage()"
                            ></button>

                            <ul class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="form-login" action="{{ route('login') }}" method="post" class="mt-6" onsubmit="setLoader()">
                        <fieldset id="fieldset">
                            @csrf
                            <div class="field mb-5">
                                <label class="label">
                                    {{ __('Email') }}
                                </label>
                                <div class="control">
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        class="input @error('email') is-danger @enderror"
                                        placeholder="{{ __('Enter your email') }}"
                                        required
                                    >
                                </div>
                                @error('email')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field mb-5">
                                <label class="label">
                                    {{ __('Password') }}
                                </label>
                                <div class="field has-addons">
                                    <div class="control is-expanded">
                                        <input
                                            type="password"
                                            name="password"
                                            id="input-password"
                                            class="input @error('password') is-danger @enderror"
                                            placeholder="{{ __('Enter your password') }}"
                                            required
                                        >
                                    </div>
                                    <div class="control icon-password" onclick="showHidePassword(this)" data-target="input-password">
                                        <button type="button" class="button" tabindex="-1">
                                            <span class="icon">
                                                <x-icon icon="fa-eye" />
                                            </span>
                                        </button>
                                        <button type="button" class="button is-hidden" tabindex="-1">
                                            <span class="icon">
                                                <x-icon icon="fa-eye-slash" />
                                            </span>
                                        </button>
                                    </div>
                                </div>
                                @error('password')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field is-horizontal mb-5">
                                <div class="field-body">
                                    <div class="field">
                                        <div class="control">
                                            <label class="checkbox">
                                                <input type="checkbox" class="mr-3" name="remember">
                                                {{ __('Remember me') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class="field has-text-right">
                                        <a href="{{ route('password.request') }}" class="has-text-primary">
                                            {{ __('Forgot password?') }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <x-recaptcha
                                action="login"
                                form-id="form-login"
                            />

                            <button class="button is-medium is-primary is-fullwidth">
                                <span class="has-text-weight-bold">
                                    {{ __('Log In') }}
                                </span>
                            </button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('bottom_scripts')
        @if ($errors->any())
            <script>
                showForm();
            </script>
        @endif

        <script>
            function removeErrorMessage() {
                const errorMessageElement = document.getElementsByClassName('error-message');

                for (let i = 0; i < errorMessageElement.length; i++) {
                    errorMessageElement[i].remove();
                }
            }
        </script>
    @endpush
</x-layouts.auth>
