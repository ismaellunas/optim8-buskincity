@inject('loginService', 'App\Services\LoginService')

<x-layouts.auth>
    <div class="column is-7-desktop is-6-tablet is-12-mobile">
        <div class="level is-mobile">
            <div class="level-left">
                <div class="level-item">
                    <a onclick="backOrOpenSocialMediaForm()">
                        <span class="icon-text">
                            <x-icon icon="fa-arrow-left" />

                            <span>{{ __('Back') }}</span>
                        </span>
                    </a>
                </div>
            </div>

            <div class="level-right">
                <div class="level-item">
                    <span class=mr-3>
                        {{ __('Already have an account?') }}
                    </span>
                    <a
                        href="{{ route('login') }}"
                        class="button is-info is-outlined"
                    >
                        {{ __('Login') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="columns is-multiline is-mobile mt-6" id="socialMediaForm">
            <div class="column is-8-desktop is-offset-2-desktop is-12-tablet is-12-mobile">
                <h1 class="title">{{ __('Sign Up') }}</h1>
                <h2 class="subtitle">
                    <span>
                        {{ __('Please choose a method to continue.') }}
                    </span>
                </h2>

                @if (session('failed'))
                    <div class="notification is-danger">
                        {{ session('failed') }}
                    </div>
                @endif

                @php
                    $availableSocialiteDrivers = $loginService->getAvailableSocialiteDrivers();
                @endphp

                @if (!empty($availableSocialiteDrivers))
                    @foreach ($availableSocialiteDrivers as $driver)
                        <a
                            href="{{ route('oauth.redirect', $driver) }}"
                            class="button is-medium is-fullwidth mt-4"
                        >
                            <span class="icon-text">
                                <x-icon icon="fa-brands fa-{{ $driver }}" />

                                <span>
                                    Continue with <span class="has-text-weight-bold">{{ Str::title($driver) }}</span>
                                </span>
                            </span>
                        </a>
                    @endforeach

                    <div class="is-divider mt-6 mb-6 ml-5 mr-6" data-content="OR"></div>
                @endif

                <a
                    href="#"
                    class="button is-medium is-fullwidth"
                    onclick="showForm()"
                >
                    <span class="icon-text">
                        <x-icon icon="fa-envelope" />

                        <span>
                            Continue with <span class="has-text-weight-bold">Email</span>
                        </span>
                    </span>
                </a>
            </div>
        </div>
        <div class="columns is-multiline is-mobile mt-6 is-hidden" id="formFields">
            <div class="column is-8-desktop is-offset-2-desktop is-12-tablet is-12-mobile">
                <h1 class="title">
                    {{ __('Create Account') }}
                </h1>

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

                    <form id="form-register" action="{{ route('register') }}" method="post">
                        @csrf

                        <div class="field">
                            <label class="label">First Name*</label>
                            <div class="control">
                                <input type="text" name="first_name" value="{{ old('first_name') }}" class="input" placeholder="Enter your first name" required>
                            </div>
                            @error('first_name')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label">Last Name*</label>
                            <div class="control">
                                <input type="text" name="last_name" value="{{ old('last_name') }}" class="input" placeholder="Enter your last name" required>
                            </div>
                            @error('last_name')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label">Email*</label>
                            <div class="control">
                                <input type="email" name="email" value="{{ old('email') }}" class="input" placeholder="Enter your email" required>
                            </div>
                            @error('email')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label">Password*</label>
                            <div class="control">
                                <div class="field has-addons mb-0">
                                    <div class="control is-expanded">
                                        <input type="password" name="password" id="input-password" class="input" placeholder="Enter your password" required>
                                    </div>
                                    <div class="control icon-password" onclick="showHidePassword(this)" data-target="input-password">
                                        <button type="button" class="button" tabindex="-1">
                                            <x-icon icon="fa-eye" />
                                        </button>
                                        <button type="button" class="button is-hidden" tabindex="-1">
                                            <x-icon icon="fa-eye-slash" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @error('password')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-recaptcha
                            action="register"
                            form-id="form-register"
                        />

                        <p>
                            By clicking on <span class="has-text-weight-bold">Create Account</span> you agree with our Terms and Conditions
                        </p>

                        <div class="field mt-4">
                            <div class="columns">
                                <div class="column is-12 has-text-right">
                                    <button type="submit" class="button is-info">
                                        {{ __('Create Account') }}
                                    </button>
                                </div>
                            </div>
                        </div>
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
            function removeErrorMessage(element) { element.parentElement.remove() };
        </script>
    @endpush
</x-layouts.auth>