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
        </script>
    @endpush

</x-layouts.blank>
