<x-layouts.master>
    <x-slot name="title">
        Donation - Success
    </x-slot>

    <div class="columns is-mobile is-centered section">
      <div class="column is-8 has-text-centered">
        <h1 class="title">
            Thank You | tack själv!

        </h1>

        <div class="content">
            <blockquote>
            They say that money can’t buy you love, and that’s a fact. However, you have shown your love for others through your donation. Thank you for creating a way to help so many people.
            </blockquote>

            <a
                class="button is-link is-large"
                href="{{ route('frontend.profiles', [$user->id]) }}"
            >
                <span>
                    {{ $user->fullName }}
                </span>
                <span class="icon">
                    <i class="far fa-kiss-wink-heart"></i>
                </span>
            </a>
        </div>
      </div>
    </div>
</x-layouts.master>
