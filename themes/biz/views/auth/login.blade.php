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
                        Don't have an account?
                    </span>
                    <a href="{{ route('register') }}">
                        Sign Up
                    </a>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="columns" id="socialMediaForm">
                <div class="column is-9 is-offset-1">
                    <h1 class="title">Log In</h1>
                    <h2 class="subtitle">
                        <span>Please login to continue</span>
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
                        <a class="box" onclick="showForm()">
                            <i class="fas fa-envelope"></i> Continue with <b>Email</b>
                        </a>
                    </div>
                </div>
            </div>
            <div class="columns is-hidden" id="formFields">
                <div class="column is-9 is-offset-1">
                    <h1 class="title">
                        Welcome Back
                    </h1>
                    <h2 class="subtitle">
                        <span>Lorem ipsum dolor sit amet.</span>
                    </h2>
                    <div class="has-text-left">

                        <form action="{{ route('login') }}" method="post" onsubmit="setLoader()">
                            @csrf
                            <div class="field">
                                <label class="label">Email</label>
                                <div class="control">
                                    <input type="email" name="email" value="{{ old('email') }}" class="input" placeholder="Enter your email" required>
                                </div>
                                @error('email')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="label">Password</label>
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

                            <div class="field columns">
                                <div class="column has-text-left">
                                    <label class="checkbox">
                                        <input type="checkbox" name="remember">
                                        <span class="pl-1">Remember me</span>
                                    </label>
                                </div>
                                <div class="column has-text-right">
                                    <a href="{{ route('password.request') }}">
                                        Forgot your password?
                                    </a>
                                </div>
                            </div>

                            <button type="submit" class="button is-block is-info is-fullwidth">
                                Log In <i class="fas fa-sign-in-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.auth>
