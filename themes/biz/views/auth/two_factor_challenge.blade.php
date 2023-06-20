<x-layouts.auth>
    <div class="column is-7-desktop is-6-tablet is-12-mobile">
        <div class="level is-mobile">
            <div class="level-left">
                <div class="level-item">
                    <a href="{{ route('login') }}">
                        <span class="icon-text">
                            <x-icon icon="fa-arrow-left" />

                            <span>{{ __('Back') }}</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <div class="columns is-multiline is-mobile" id="formFields">
            <div class="column is-8-desktop is-offset-2-desktop is-12-tablet is-12-mobile">
                <div class="mb-4">
                    <p class="recovery" data-target="recovery-code">
                        {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
                    </p>

                    <p class="recovery is-hidden" data-target="code">
                        {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
                    </p>
                </div>

                @if (session('failed'))
                    <div class="notification is-danger">
                        {{ session('failed') }}
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

                <form id="form-tfa" action="{{ route('two-factor.login') }}" method="post">
                    @csrf
                    <div id="code" class="field mb-5 recovery">
                        <label class="label">Code</label>
                        <div class="control">
                            <input type="text" name="code" value="{{ old('code') }}" class="input">
                        </div>
                        @error('code')
                            <p class="help is-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="recovery-code" class="field mb-5 recovery is-hidden">
                        <label class="label">Recovery Code</label>
                        <div class="control">
                            <input type="text" name="recovery_code" value="{{ old('recovery_code') }}" class="input">
                        </div>
                        @error('recovery_code')
                            <p class="help is-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-recaptcha
                        action="tfa"
                        form-id="form-tfa"
                    />

                    <div class="buttons">
                        <button type="button" class="button recovery" data-target="recovery-code" onclick="toggleRecovery(this)">
                            {{ __('Use a recovery code')}}
                        </button>

                        <button type="button" class="button recovery is-hidden" data-target="code" onclick="toggleRecovery(this)">
                            {{ __('Use an authentication code')}}
                        </button>

                        <button type="submit" class="button is-info">
                            {{ __('Log In')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('bottom_scripts')
        <script>
            function toggleRecovery(identifier) {
                const target = identifier.getAttribute('data-target');
                const inputCode = document.getElementById(target);

                document.querySelectorAll('.recovery').forEach(function (el) {
                    if (!el.classList.contains('is-hidden')) {
                        el.classList.add('is-hidden');
                    }

                    if (
                        el.getAttribute('data-target')
                        && el.getAttribute('data-target') != target
                    ) {
                        el.classList.remove('is-hidden');
                    }

                    for (let i = 0; i < el.getElementsByTagName('input').length; i++) {
                        el.getElementsByTagName('input')[i].value = '';
                    }
                });

                inputCode.classList.remove('is-hidden');
            }

            function removeErrorMessage(element) { element.parentElement.remove() };
        </script>
    @endpush
</x-layouts.auth>