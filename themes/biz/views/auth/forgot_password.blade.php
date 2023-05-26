<x-layouts.auth>
    <div class="column is-three-fifths has-text-left">
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <a href="{{ route('login') }}">
                        <span class="icon"><i class="fas fa-arrow-left"></i></span>
                        <span>Back</span>
                    </a>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="columns" id="formFields">
                <div class="column is-9 is-offset-1">
                    <div class="mb-4">
                        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
                    </div>

                    @if (session()->has('failed'))
                        <div class="notification is-danger">
                            {{ session()->get('failed') }}
                        </div>
                    @endif

                    @if (session()->has('status'))
                        <div class="notification is-primary">
                            {{ session()->get('status') }}
                        </div>
                    @endif

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

                    <form method="post" action="{{ route('password.email') }}" onsubmit="setLoader()">
                        <div class="mb-4">
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
                        </div>

                        <x-recaptcha action="forgot_password" />

                        <div class="mt-4">
                            <button type="submit" class="button is-info">
                                Email Password Reset Link
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    @push('bottom_scripts')
        <script>
            function removeErrorMessage(element) { element.parentElement.remove() };
        </script>
    @endpush
</x-layouts.auth>