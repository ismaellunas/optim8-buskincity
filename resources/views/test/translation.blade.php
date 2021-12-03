<x-layouts.master>
    @push('metas')
        <meta head-key="description"
            name="description"
            content="{{ config('app.name') }}"
        />
    @endpush

    <x-slot name="title">
        {{ $title }}
    </x-slot>

    <section class="section">
        <div class="container">
            <div class="content">
                <h1>Test Translation</h1>
                <h3>Auth</h3>
                <ul>
                    <li><b>Password:</b> @lang('auth.password')</li>
                    <li><b>Failed:</b> @lang('auth.failed')</li>
                    <li><b>Throttle:</b> @lang('auth.throttle')</li>
                </ul>
                <hr>
                <h3>Passwords</h3>
                <p>
                    <ul>
                        <li><b>Reset:</b> @lang('passwords.reset')</li>
                        <li><b>Sent:</b> @lang('passwords.sent')</li>
                        <li><b>User:</b> @lang('passwords.user')</li>
                    </ul>
                </p>
            </div>
        </div>
    </section>
</x-layouts.master>
