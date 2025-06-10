<x-layouts.auth>
    <x-slot name="title">
        {{ __('Signup | :appName', ['appName' => config('app.name')]) }}
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
                    <a
                        class="button is-white"
                        @if ($isSocialiteDriverExists)
                            onclick="backOrOpenSocialMediaForm()"
                        @else
                            href="{{ route('homepage') }}"
                        @endif
                    >
                        <x-icon icon="fa-arrow-left" is-small />
                        <span class="has-text-weight-bold">
                            {{ __('Back') }}
                        </span>
                    </a>
                </div>

                <!-- Right side -->
                <div class="level-right">
                    <span>
                        {{ __('Already have an account?') }}
                    </span>
                    <a href="{{ route('login') }}" class="button is-primary is-outlined is-responsive ml-4">
                        <span class="has-text-weight-bold">
                            {{ __('Log In') }}
                        </span>
                    </a>
                </div>
            </nav>

            @if (session('failed'))
                <div class="columns is-mobile is-vcentered is-flex-grow-1">
                    <div class="column is-8-desktop is-offset-2-desktop is-10-tablet is-offset-1-tablet is-12-mobile">
                        <div class="notification is-danger">
                            {{ session('failed') }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="columns is-mobile is-vcentered is-flex-grow-1">
                <div
                    id="socialMediaForm"
                    @class([
                        'column is-8-desktop is-offset-2-desktop is-10-tablet is-offset-1-tablet is-12-mobile',
                        'is-hidden' => ! $isSocialiteDriverExists
                    ])
                >
                    <h1 class="title is-2 mb-4">
                        {{ __('Sign Up') }}
                    </h1>
                    <p>
                        {{ __('Please choose a method to continue.') }}
                    </p>

                    @include('auth.social-driver-list')
                </div>

                <div
                    id="formFields"
                    @class([
                        'column is-8-desktop is-offset-2-desktop is-10-tablet is-offset-1-tablet is-12-mobile',
                        'is-hidden' => $isSocialiteDriverExists
                    ])
                >
                    <h1 class="title is-2 mb-4">
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

                    <form id="form-register" action="{{ route('register') }}" method="post" class="mt-4" onsubmit="setLoader()">
                        <fieldset id="fieldset">

                        @csrf
                        <div class="field is-horizontal mb-5">
                            <div class="field-body">
                                <div class="field">
                                    <label class="label">
                                        {{ __('First name') }}
                                        <sup class="has-text-danger">*</sup>
                                    </label>
                                    <div class="control">
                                        <input
                                            type="text"
                                            name="first_name"
                                            value="{{ old('first_name') }}"
                                            class="input @error('first_name') is-danger @enderror"
                                            placeholder="{{ __('First name') }}"
                                            required
                                        >
                                    </div>
                                    @error('first_name')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="field">
                                    <label class="label">
                                        {{ __('Last name') }}
                                        <sup class="has-text-danger">*</sup>
                                    </label>
                                    <div class="control">
                                        <input
                                            type="text"
                                            name="last_name"
                                            value="{{ old('last_name') }}"
                                            class="input @error('last_name') is-danger @enderror"
                                            placeholder="{{ __('Last name') }}"
                                            required
                                        >
                                    </div>
                                    @error('last_name')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="field mb-5">
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

                        <div class="field mb-5">
                            <label class="label">
                                {{ __('Password') }}
                                <sup class="has-text-danger">*</sup>
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
                                            <i class="fa-solid fa-eye"></i>
                                        </span>
                                    </button>
                                    <button type="button" class="button is-hidden" tabindex="-1">
                                        <span class="icon">
                                            <i class="fa-solid fa-eye-slash"></i>
                                        </span>
                                    </button>
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

                        <div class="field is-horizontal mb-5">
                            <div class="field-body">
                                <div class="field">
                                    <div class="pr-6">
                                        <p class="is-size-7">
                                            {!! __('By clicking on Create Account you agree with our :link', ['link' => '<a href="#">Terms and Conditions</a>']) !!}
                                        </p>
                                    </div>
                                </div>

                                <div class="field">
                                    <button class="button is-medium is-primary is-fullwidth">
                                        <span class="has-text-weight-bold">
                                            {{ __('Create Account') }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        @push('bottom_scripts')
            <script>
                showForm();

                function removeErrorMessage(element) { element.parentElement.remove() };
            </script>
        @endpush
    @endif
</x-layouts.auth>
