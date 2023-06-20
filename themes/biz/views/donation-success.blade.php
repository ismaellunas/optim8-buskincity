<x-layouts.master-basic>
    <x-slot name="title">
        {{ __('Donation') . ' - ' . __('Success') }}
    </x-slot>

    <div class="section">
        <div class="columns is-multiline is-mobile is-centered">
            <div class="column is-8-desktop is-12-tablet is-12-mobile has-text-centered">
                <h1 class="title">
                    {{ __('Thank You') }}
                </h1>

                <div class="content">
                    <blockquote>
                        {{ __('They say that money can’t buy you love, and that’s a fact.') }} <br/>
                        {{ __('However, you have shown your love for others through your donation. Thank you for creating a way to help so many people.') }}
                    </blockquote>

                    <a
                        class="button is-primary is-large"
                        href="{{ $user->profilePageUrl }}"
                    >
                        <span>
                            {{ $user->fullName }}
                        </span>
                        <span class="icon">
                            <x-icon icon="fa-kiss-wink-heart" />
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.master-basic>
