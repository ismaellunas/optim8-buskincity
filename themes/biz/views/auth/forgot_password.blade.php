<x-layouts.auth>
    <div class="column is-7-desktop is-6-tablet is-12-mobile">
        <div class="level is-mobile">
            <div class="level-left">
                <div class="level-item">
                    <a href="{{ route('login') }}">
                        <span class="icon">
                            <i class="fas fa-arrow-left"></i>
                        </span>

                        <span>{{ __('Back') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="columns is-multiline is-mobile" id="formFields">
            <div class="column is-8-desktop is-offset-2-desktop is-12-tablet is-12-mobile">
                <div class="mb-4">
                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                </div>

                @if (session()->has('failed'))
                    <div class="notification is-danger">
                        {{ session()->get('failed') }}
                    </div>
                @endif

                @if (session()->has('status'))
                    <div class="notification is-info">
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

                <form id="form-forgot-password" method="post" action="{{ route('password.email') }}">
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

                    <x-recaptcha
                        action="forgot_password"
                        form-id="form-forgot-password"
                    />

                    <div class="mt-4">
                        <button type="submit" class="button is-info">
                            Email Password Reset Link
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('bottom_scripts')
        <script>
            function removeErrorMessage(element) { element.parentElement.remove() };
        </script>
    @endpush
</x-layouts.auth>