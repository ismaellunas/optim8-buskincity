@inject('loginService', 'App\Services\LoginService')

<x-layouts.auth>
    <x-slot name="title">
        Signup | {{ config('app.name') }}
    </x-slot>

    <div class="columns">
        <div class="column is-5 is-hidden-mobile">
            <img src="{{ url('/themes/buskincity/images/login.jpg') }}" alt="BuskinCity buskers performing on the street" class="is-radius">
        </div>
        <div class="is-flex is-flex-direction-column column is-7">
            <nav class="level is-mobile">
                <!-- Left side -->
                <div class="level-left">
                    <a onclick="backOrOpenSocialMediaForm()" class="button is-white">
                        <span class="icon is-small">
                            <i class="fa-regular fa-arrow-left"></i>
                        </span>
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

            <div class="columns is-vcentered is-flex-grow-1">
                <div id="socialMediaForm" class="column is-8 is-offset-2">
                    <h1 class="title is-2 mb-4">Sign Up</h1>
                    <p>Please choose a method to continue.</p>

                    @foreach ($loginService->getAvailableSocialiteDrivers() as $driver)
                        <a href="{{ route('oauth.redirect', $driver) }}" @class([
                            "button is-medium is-light is-fullwidth",
                            "mt-6" => $loop->first,
                            "mt-4" => !$loop->first,
                        ])>
                            <span class="icon is-small">
                                <i class="fa-brands fa-{{ $driver }}"></i>
                            </span>
                            <span>Sign up with <span class="has-text-weight-bold">{{ Str::title($driver) }}</span></span>
                        </a>
                    @endforeach

                    <div class="is-divider mt-6 mb-6 ml-5 mr-6" data-content="OR"></div>

                    <a class="button is-medium is-light is-fullwidth mt-4" onclick="showForm()">
                        <span class="icon is-small">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <span>Sign up with <span class="has-text-weight-bold">Email</span></span>
                    </a>
                </div>

                <div id="formFields" class="column is-8 is-offset-2 is-hidden">
                    <h1 class="title is-2 mb-4">Create Account</h1>
                    <p>Fill in your email and password to login.</p>

                    <form action="{{ route('register') }}" method="post" class="mt-6" onsubmit="setLoader()">
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
                                            <i class="fa-light fa-eye"></i>
                                        </span>
                                    </button>
                                    <button type="button" class="button is-hidden" tabindex="-1">
                                        <span class="icon">
                                            <i class="fa-light fa-eye-slash"></i>
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
    @endpush
</x-layouts.auth>
