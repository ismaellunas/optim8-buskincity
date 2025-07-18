<x-layouts.auth>
    <div class="column is-7-desktop is-6-tablet is-12-mobile">
        <div class="level is-mobile">
            <div class="level-left">
                <div class="level-item">
                    <a href="{{ route('password.request') }}">
                        <span class="icon-text">
                            <x-icon icon="fa-arrow-left" />

                            <span>{{ __('Back') }}</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <div class="columns is-multiline is-mobile">
            <div class="column is-8-desktop is-offset-2-desktop is-12-tablet is-12-mobile">
                <div class="mb-4">
                    <h1 class="title">
                        {{ __('Reset Password') }}
                    </h1>
                    <h2 class="subtitle">
                        <span>
                            {{ __('Please fill the password and password confirmation to continue.') }}
                        </span>
                    </h2>
                </div>

                <form method="post" action="{{ route('password.update') }}" onsubmit="setLoader()">
                    <div>
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="field">
                            <label class="label">
                                {{ __('Email') }}
                            </label>
                            <div class="control">
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ $email }}"
                                    class="input @error('email') is-danger @enderror"
                                    placeholder="{{ __('Enter your email') }}"
                                    required
                                    disabled
                                >
                            </div>
                            @error('email')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label">
                                {{ __('Password') }}
                            </label>
                            <div class="control">
                                <div class="field has-addons mb-0">
                                    <div class="control is-expanded">
                                        <input
                                            type="password"
                                            name="password"
                                            id="input-password"
                                            class="input @error('password') is-danger @enderror"
                                            placeholder="{{ __('Enter new password') }}"
                                            required
                                        >
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

                        <div class="field">
                            <label class="label">
                                {{ __('Confirm Password') }}
                            </label>
                            <div class="control">
                                <div class="field has-addons mb-0">
                                    <div class="control is-expanded">
                                        <input
                                            type="password"
                                            name="password_confirmation"
                                            id="input-password-confirmation"
                                            class="input @error('password_confirmation') is-danger @enderror"
                                            placeholder="{{ __('Enter password confirmation') }}"
                                            required
                                        >
                                    </div>
                                    <div class="control icon-password" onclick="showHidePassword(this)" data-target="input-password-confirmation">
                                        <button type="button" class="button" tabindex="-1">
                                            <x-icon icon="fa-eye" />
                                        </button>
                                        <button type="button" class="button is-hidden" tabindex="-1">
                                            <x-icon icon="fa-eye-slash" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @error('password_confirmation')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="button is-info">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.auth>