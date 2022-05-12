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
                        <p class="recovery" data-target="recovery-code">
                            {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
                        </p>

                        <p class="recovery is-hidden" data-target="code">
                            {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
                        </p>
                    </div>

                    @error('email')
                        <div class="notification is-danger mt-4">
                            {{ $message }}
                        </div>
                    @endif

                    <form action="{{ route('two-factor.login') }}" onsubmit="setLoader()" method="post" class="mt-6">
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
        </section>
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
        </script>
    @endpush
</x-layouts.auth>