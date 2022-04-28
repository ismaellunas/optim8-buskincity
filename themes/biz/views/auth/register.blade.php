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
                        Already have an account?
                    </span>
                    <a href="{{ route('login') }}">
                        Login
                    </a>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="columns" id="socialMediaForm">
                <div class="column is-9 is-offset-1">
                    <h1 class="title">Sign Up</h1>
                    <h2 class="subtitle">
                        <span>Are you performer? </span>
                        <span>Sign Up Here</span>
                    </h2>
                    <div class="has-text-centered">
                        @foreach ($loginService->getAvailableSocialiteDrivers() as $driver)
                            <a href="{{ route('oauth.redirect', $driver) }}" class="box">
                                <i class="fab fa-{{ $driver }}"></i> Continue with <b>{{ Str::title($driver) }}</b>
                            </a>
                        @endforeach

                        <div class="h-line-wrapper">
                            <span class="h-line-words">or</span>
                        </div>
                        <a class="box" onclick="showForm()">
                            <i class="fas fa-envelope"></i> Continue with <b>Email</b>
                        </a>
                    </div>
                </div>
            </div>
            <div class="columns is-hidden" id="formFields">
                <div class="column is-9 is-offset-1">
                    <h1 class="title">
                        Create Account
                    </h1>
                    <h2 class="subtitle">
                        <span>Lorem ipsum dolor sit amet.</span>
                    </h2>
                    <div class="has-text-left">

                        <form action="{{ route('register') }}" method="post" onsubmit="setLoader()">
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

                            <div class="field">
                                <label class="label">Password Confirmation*</label>
                                <div class="control">
                                    <div class="field has-addons mb-0">
                                        <div class="control is-expanded">
                                            <input type="password" name="password_confirmation" id="input-password-confirmation" class="input" placeholder="Confirm Password" required>
                                        </div>
                                        <div class="control icon-password" onclick="showHidePassword(this)" data-target="input-password-confirmation">
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
                                @error('password_confirmation')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>


                            <div class="flex mt-4">
                                <div class="columns is-gapless">
                                    <div class="column is-two-thirds">
                                        <span>
                                            By clicking on <b>Create Account</b> you agree with our Terms and Conditions
                                        </span>
                                    </div>
                                    <div class="column is-one-third has-text-right">
                                        <button type="submit" class="button is-info">
                                            Create Account
                                        </button>
                                    </div>
                                </div>
                            </div>

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
    @endpush
</x-layouts.auth>