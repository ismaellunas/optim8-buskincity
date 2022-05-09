<x-layouts.auth>
    <x-slot name="title">
        {{ __('Two Factor Authentication') }} | {{ config('app.name') }}
    </x-slot>

    <div class="columns">
        <div class="column is-5 is-hidden-mobile">
            <img src="{{ url('/themes/buskincity/images/login.jpg') }}" alt="BuskinCity buskers performing on the street" class="is-radius">
        </div>
        <div class="is-flex is-flex-direction-column column is-7">
            <nav class="level is-mobile">
                <!-- Left side -->
                <div class="level-left">
                    <a href="{{ route('login') }}"  class="button is-white">
                        <span class="icon is-small">
                            <i class="fa-regular fa-arrow-left"></i>
                        </span>
                        <span class="has-text-weight-bold">{{ __('Back') }}</span>
                    </a>
                </div>
            </nav>

            <div class="columns is-vcentered is-flex-grow-1">
                <div class="column is-8 is-offset-2">
                    <h1 class="title is-2 mb-4">{{ __('Two Factor Authentication') }}</h1>
                    <p class="recovery" data-target="recovery-code">
                        {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
                    </p>

                    <p class="recovery is-hidden" data-target="code">
                        {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
                    </p>

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
                            <button type="button" class="button is-medium recovery" data-target="recovery-code" onclick="toggleRecovery(this)">
                                <span class="has-text-weight-bold">
                                    {{ __('Use a recovery code')}}
                                </span>
                            </button>

                            <button type="button" class="button is-medium recovery is-hidden" data-target="code" onclick="toggleRecovery(this)">
                                <span class="has-text-weight-bold">
                                    {{ __('Use an authentication code')}}
                                </span>
                            </button>

                            <button type="submit" class="button is-medium is-primary">
                                <span class="has-text-weight-bold">
                                    {{ __('Log In')}}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
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
        </script>
    @endpush
</x-layouts.auth>