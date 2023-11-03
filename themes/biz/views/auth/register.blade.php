<x-layouts.auth>
    <div class="column is-7-desktop is-6-tablet is-12-mobile">
        <div class="level is-mobile">
            <div class="level-left">
                <div class="level-item">
                    <a
                        @if ($isSocialiteDriverExists)
                            onclick="backOrOpenSocialMediaForm()"
                        @else
                            href="{{ route('homepage') }}"
                        @endif
                    >
                        <span class="icon-text">
                            <x-icon icon-key="back" />

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

        <div
            id="socialMediaForm"
            @class([
                'columns is-multiline is-mobile mt-6',
                'is-hidden' => ! $isSocialiteDriverExists
            ])
        >
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

                @include('auth.social-driver-list')
            </div>
        </div>

        <div
            id="formFields"
            @class([
                'columns is-multiline is-mobile mt-6',
                'is-hidden' => $isSocialiteDriverExists
            ])
        >
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

                    <form id="form-register" action="{{ route('register') }}" method="post" onsubmit="setLoader()">
                        @csrf

                        <div class="field">
                            <label class="label">
                                {{ __('First Name') }}
                                <sup class="has-text-danger">*</sup>
                            </label>
                            <div class="control">
                                <input
                                    type="text"
                                    name="first_name"
                                    value="{{ old('first_name') }}"
                                    class="input @error('first_name') is-danger @enderror"
                                    placeholder="{{ __('Enter your first name') }}"
                                    required
                                >
                            </div>
                            @error('first_name')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label">
                                {{ __('Last Name') }}
                                <sup class="has-text-danger">*</sup>
                            </label>
                            <div class="control">
                                <input
                                    type="text"
                                    name="last_name"
                                    value="{{ old('last_name') }}"
                                    class="input @error('last_name') is-danger @enderror"
                                    placeholder="{{ __('Enter your last name') }}"
                                    required
                                >
                            </div>
                            @error('last_name')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label">
                                {{ __('Email') }}
                                <sup class="has-text-danger">*</sup>
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

                        <div class="field">
                            <label class="label">
                                {{ __('Password') }}
                                <sup class="has-text-danger">*</sup>
                            </label>
                            <div class="control">
                                <div class="field has-addons mb-0">
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
                                            <x-icon icon-key="eye" />
                                        </button>
                                        <button type="button" class="button is-hidden" tabindex="-1">
                                            <x-icon icon-key="eyeSlash" />
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
                            {!! __('By clicking on :button you agree with our Terms and Conditions', ['button' => '<span class="has-text-weight-bold">Create Account</span>']) !!}
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
