<x-layouts.blank>
    <section class="hero is-fullheight">
        <div class="hero-body">
            <div class="container has-text-centered">
                <div class="columns">
                    <div class="column is-two-fifths has-text-left">
                        <div class="card">
                            <div class="card-image">
                                <figure class="image is-3by4">
                                    <img src="https://dummyimage.com/550x715/e5e5e5/ffffff.jpg">
                                </figure>
                            </div>
                        </div>
                    </div>
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
                                        <a href="{{ route('oauth.redirect', 'google') }}" class="box">
                                            <i class="fab fa-google"></i> Continue with <b>Google</b>
                                        </a>
                                        <a href="{{ route('oauth.redirect', 'facebook') }}" class="box">
                                            <i class="fab fa-facebook"></i> Continue with <b>Facebook</b>
                                        </a>
                                        <a href="{{ route('oauth.redirect', 'twitter') }}" class="box">
                                            <i class="fab fa-twitter"></i> Continue with <b>Twitter</b>
                                        </a>

                                        <div class="h-line-wrapper">
                                            <span class="h-line-words">or</span>
                                        </div>
                                        <a class="box" onclick="showRegisterForm()">
                                            <i class="fas fa-envelope"></i> Continue with <b>Email</b>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="columns is-hidden" id="registerForm">
                                <div class="column is-9 is-offset-1">
                                    <h1 class="title">
                                        Create Account
                                    </h1>
                                    <h2 class="subtitle">
                                        <span>Lorem ipsum dolor sit amet.</span>
                                    </h2>
                                    <div class="has-text-left">

                                        <form action="{{ route('register') }}" method="post">
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
                                                            <input type="password" name="password" class="input input-password" placeholder="Enter your password" required>
                                                        </div>
                                                        <div class="control icon-password" onclick="showHidePassword()">
                                                            <button type="button" class="button">
                                                                <span class="icon">
                                                                    <i class="fas fa-eye"></i>
                                                                </span>
                                                            </button>
                                                            <button type="button" class="button is-hidden">
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
                                                            <input type="password" name="password_confirmation" class="input input-password" placeholder="Confirm Password" required>
                                                        </div>
                                                        <div class="control icon-password" onclick="showHidePassword()">
                                                            <button type="button" class="button">
                                                                <span class="icon">
                                                                    <i class="fas fa-eye"></i>
                                                                </span>
                                                            </button>
                                                            <button type="button" class="button is-hidden">
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
                                                        </button type="submit">
                                                    </div>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('bottom_scripts')
        @if ($errors->any())
            <script>
                document.getElementById('registerForm').classList.remove('is-hidden');
                document.getElementById('socialMediaForm').classList.add('is-hidden');
            </script>
        @endif

        <script>
            function showRegisterForm() {
                document.getElementById('registerForm').classList.remove('is-hidden');
                document.getElementById('socialMediaForm').classList.add('is-hidden');
            }

            function backOrOpenSocialMediaForm() {
                let registerForm = document.getElementById('registerForm');
                if (registerForm.classList.contains('is-hidden')) {
                    window.location.href = "{{ url('/') }}"
                }
                document.getElementById('registerForm').classList.add('is-hidden');
                document.getElementById('socialMediaForm').classList.remove('is-hidden');
            }

            function showHidePassword() {
                document.querySelectorAll('.input-password').forEach(function(el) {
                    if (el.getAttribute('type') === 'password') {
                        el.setAttribute('type', 'text');
                    } else {
                        el.setAttribute('type', 'password');
                    }
                });
                document.querySelectorAll('.icon-password .button').forEach(function(e) {
                    if (e.classList.contains('is-hidden')) {
                        e.classList.remove('is-hidden');
                    } else {
                        e.classList.add('is-hidden');
                    }

                });
            }
        </script>
    @endpush

</x-layouts.blank>
