@inject('userProfile', 'App\Services\UserProfileService')
@php
    $countryCode = $userProfile->getMeta('country');
    $flagUrl = $countryCode ? url('/images/flags/'.strtolower($countryCode).'.svg') : null;
@endphp

<x-layouts.master>
    <x-slot name="title">
        {{ $user->fullName . ' | ' .config('app.name') }}
    </x-slot>

    <x-slot name="metaDescription">
        {{ Str::limit($userProfile->getMeta('short_description', $locale) ?? __('Public profile.'), 155, ' ...') }}
    </x-slot>

    <div class="b752-public-profile section is-small theme-font">
        <div class="container">
            <div class="columns is-multiline is-centered">
                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                    <div class="profile-background hero is-medium is-primary is-radius" style="background-image: url({{ $userProfile->getCoverBackgroundUrl(1280, 398) }});">
                        <div class="hero-body">
                            <div class="title">&nbsp;</div>
                            <div class="subtitle">&nbsp;</div>
                        </div>
                    </div>
                </div>

                <div class="column is-11-desktop is-11-tablet is-11-mobile">
                    <figure class="profile-picture image is-250x250">
                        <x-image
                            src="{{ $user->optimizedProfilePhotoOrDefaultUrl }}"
                            alt="{{ $user->fullName }}"
                            width="250"
                            height="250"
                            rounded="is-rounded"
                            is-lazyload
                        />

                        @if ($flagUrl)
                        <span class="flag">
                            <x-image
                                src="{{ $flagUrl }}"
                                alt=""
                                width="60"
                                height="60"
                                rounded="is-rounded"
                                is-lazyload
                            />
                        </span>
                        @endif
                    </figure>
                </div>

                <div class="column is-11-desktop is-12-tablet is-12-mobile">
                    <h1 class="title is-2 mt-5 mb-2">{{ $user->fullName }}</h1>

                    <div class="columns is-multiline is-mobile mt-3">
                        <div class="column is-8-desktop is-12-tablet is-12-mobile">
                            <div class="content">
                                <p>{{ $userProfile->getMeta('short_description', $locale) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('themes/'.config('theme.parent').'/js/basic.js')
    @endpush
</x-layouts.master>
