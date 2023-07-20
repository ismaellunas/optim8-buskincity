<x-layouts.auth>
    <x-slot name="title">
        {{ __('Reset Password') }} | {{ config('app.name') }}
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
                    <a href="{{ route('password.request') }}" class="button is-white">
                        <x-icon icon="fa-arrow-left" is-small />
                        <span class="has-text-weight-bold">{{ __('Back') }}</span>
                    </a>
                </div>
            </nav>

            <div class="columns is-mobile is-vcentered is-flex-grow-1">
                <div class="column is-8-desktop is-offset-2-desktop is-10-tablet is-offset-1-tablet is-12-mobile">
                    <h1 class="title is-2 mb-4">
                        {{ __('Reset Password') }}
                    </h1>
                    <p>
                        {{ __('Fill in a new password and confirmation password to reset password.') }}
                    </p>

                    @if ($errors->any())
                        <div class="notification is-danger mt-4">
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

                    <form method="post" action="{{ route('password.update') }}" class="mt-6" onsubmit="setLoader()">
                        <fieldset id="fieldset">

                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email }}">

                            <div class="field mb-5">
                                <label class="label">Email</label>
                                <div class="control">
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ $email }}"
                                        class="input"
                                        placeholder="Enter your email"
                                        required
                                        disabled
                                    >
                                </div>
                                @error('email')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field mb-5">
                                <label class="label">Password</label>
                                <div class="control">
                                    <div class="field has-addons mb-0">
                                        <div class="control is-expanded">
                                            <input
                                                type="password"
                                                name="password"
                                                id="input-password"
                                                class="input"
                                                placeholder="Enter new password"
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

                            <div class="field mb-5">
                                <label class="label">Confirm Password</label>
                                <div class="control">
                                    <div class="field has-addons mb-0">
                                        <div class="control is-expanded">
                                            <input
                                                type="password"
                                                name="password_confirmation"
                                                id="input-password-confirmation"
                                                class="input"
                                                placeholder="Enter password confirmation"
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

                            <button type="submit" class="button is-medium is-primary is-fullwidth">
                                <span class="has-text-weight-bold">{{ __('Reset Password')}}</span>
                            </button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('bottom_scripts')
        <script>
            function removeErrorMessage(element) { element.parentElement.remove() };
        </script>
    @endpush
</x-layouts.auth>
