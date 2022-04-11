<x-layouts.auth>
    <div class="column is-three-fifths has-text-left">
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <a href="{{ route('password.request') }}">
                        <span class="icon"><i class="fas fa-arrow-left"></i></span>
                        <span>Back</span>
                    </a>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="columns">
                <div class="column is-9 is-offset-1">
                    <div class="mb-4">
                        <h1 class="title">
                            Reset Password
                        </h1>
                        <h2 class="subtitle">
                            <span>Lorem ipsum dolor sit amet.</span>
                        </h2>
                    </div>

                    <form method="post" action="{{ route('password.update') }}" onsubmit="setLoader()">
                        <div>
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email }}">

                            <div class="field">
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

                            <div class="field">
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
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="button is-info">
                                Reset Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</x-layouts.auth>