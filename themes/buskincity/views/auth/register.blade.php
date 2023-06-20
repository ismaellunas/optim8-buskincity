@inject('loginService', 'App\Services\LoginService')

<x-layouts.auth>
    <x-slot name="title">
        Signup | {{ config('app.name') }}
    </x-slot>

    <div class="columns is-mobile">
        <div class="column is-5-desktop is-6-tablet is-hidden-mobile">
            <img src="{{ url('/themes/buskincity/images/login.jpg') }}" alt="BuskinCity buskers performing on the street" class="is-radius">
        </div>

        <div class="is-flex is-flex-direction-column column is-7-desktop is-6-tablet is-12-mobile">
            <nav class="level is-mobile">
                <!-- Left side -->
                <div class="level-left">
                    <a onclick="backOrOpenSocialMediaForm()" class="button is-white">
                        <x-icon icon="fa-arrow-left" is-small />
                        <span class="has-text-weight-bold">Back</span>
                    </a>
                </div>

                <!-- Right side -->
                <div class="level-right">
                    <span>Already have an account?</span>
                    <a href="{{ route('login') }}" class="button is-primary is-outlined is-responsive ml-4">
                        <span class="has-text-weight-bold">Log In</span>
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
                <div id="socialMediaForm" class="column is-8-desktop is-offset-2-desktop is-10-tablet is-offset-1-tablet is-12-mobile">
                    <h1 class="title is-2 mb-4">Sign Up</h1>
                    <p>Please choose a method to continue.</p>

                    @if (!empty($loginService->getAvailableSocialiteDrivers()))
                        @foreach ($loginService->getAvailableSocialiteDrivers() as $driver)
                            <a href="{{ route('oauth.redirect', $driver) }}" @class([
                                "button is-medium is-light is-fullwidth",
                                "mt-6" => $loop->first,
                                "mt-4" => !$loop->first,
                            ])>
                                <x-icon icon="fa-brands fa-{{ $driver }}" is-small />
                                <span>Sign up with <span class="has-text-weight-bold">{{ Str::title($driver) }}</span></span>
                            </a>
                        @endforeach

                        <div class="is-divider mt-6 mb-6 ml-5 mr-6" data-content="OR"></div>
                    @endif

                    <a class="button is-medium is-light is-fullwidth mt-4" onclick="showForm()">
                        <x-icon icon="fa-envelope" is-small />
                        <span>Sign up with <span class="has-text-weight-bold">Email</span></span>
                    </a>
                </div>

                <div id="formFields" class="column is-8-desktop is-offset-2-desktop is-10-tablet is-offset-1-tablet is-12-mobile is-hidden">
                    <h1 class="title is-2 mb-4">Create Account</h1>

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

                    <form id="form-register" action="{{ route('register') }}" method="post" class="mt-4">
                        <fieldset id="fieldset">

                        @csrf
                        <div class="field is-horizontal mb-5">
                            <div class="field-body">
                                <div class="field">
                                    <label class="label">First name</label>
                                    <div class="control">
                                        <input type="text" name="first_name" value="{{ old('first_name') }}" class="input" placeholder="First name" required>
                                    </div>
                                    @error('first_name')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="field">
                                    <label class="label">Last name</label>
                                    <div class="control">
                                        <input type="text" name="last_name" value="{{ old('last_name') }}" class="input" placeholder="Last name" required>
                                    </div>
                                    @error('last_name')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="field mb-5">
                            <label class="label">Email</label>
                            <div class="control">
                                <input type="email" name="email" value="{{ old('email') }}" class="input" placeholder="Enter your email" required>
                            </div>
                            @error('email')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field mb-5">
                            <label class="label">Password</label>
                            <div class="field has-addons">
                                <div class="control is-expanded">
                                    <input type="password" name="password" id="input-password" class="input" placeholder="Enter your password" required>
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

                        <x-recaptcha
                            action="register"
                            form-id="form-register"
                        />

                        <div class="field is-horizontal mb-5">
                            <div class="field-body">
                                <div class="field">
                                    <div class="pr-6">
                                        <p class="is-size-7">By clicking on Create Account you agree with our <a href="#">Terms and Conditions</a></p>
                                    </div>
                                </div>

                                <div class="field">
                                    <button class="button is-medium is-primary is-fullwidth">
                                        <span class="has-text-weight-bold">Create Account</span>
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
