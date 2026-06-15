@inject('pageSpace', 'Modules\Space\Services\PageSpaceService')
@inject('translationService', 'App\Services\TranslationService')
@php
    $isCityPage = ($space->type?->name ?? null) === 'City';
    $pitches = $isCityPage ? $pageSpace->getCityPitches() : $pageSpace->getLeaves();
    $hasPitches = $pitches && $pitches->isNotEmpty();
@endphp

<x-layouts.master>
    <x-slot name="title">
        {{ trim($metaTitle ?? $space->name). ' | ' .config('app.name') }}
    </x-slot>

    <x-slot name="metaDescription">
        {{ $metaDescription ?? $space->name }}
    </x-slot>

    <div class="b752-public-profile section is-small theme-font">
        <div class="container">
            <div class="columns is-multiline is-mobile is-centered">
                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                    <div
                        class="profile-background hero is-medium is-primary is-radius"
                        @if ($space->cover) style="background-image: url({{ $space->getOptimizedCoverImageUrl(1280, 400) }});" @endif
                    >
                        <div class="hero-body">
                            <div class="title">&nbsp;</div>
                            <div class="subtitle">&nbsp;</div>
                        </div>
                    </div>
                </div>

                <div class="column is-11-desktop is-11-tablet is-11-mobile">
                    <figure class="profile-picture image is-250x250">
                        <x-image
                            src="{{ $space->getOptimizedLogoImageUrl(250, 250) }}"
                            alt="{{ $space->name }}"
                            width="250"
                            height="250"
                            rounded="is-rounded"
                            is-lazyload
                        />
                    </figure>
                </div>

                <div class="column is-11-desktop is-12-tablet is-12-mobile">
                    <h1 class="title is-2 mt-5 mb-2">{{ ucwords($space->name) }}</h1>

                    <p class="is-size-7">{{ $space->address }}</p>

                    <div class="columns is-multiline is-mobile mt-3">
                        <div class="column is-8-desktop is-12-tablet is-12-mobile">
                            <div class="content has-text-justified">
                                <p>{{ $space->description }}</p>
                            </div>
                        </div>

                        @if ($space->product)
                        @can ('bookAProduct', $space)
                            <div class="column is-4-desktop is-hidden-touch has-text-right">
                                <a
                                    href="{{ route('booking.products.show', $space->product->id) }}"
                                    class="button is-primary"
                                >
                                    {{ __('Book this :typeName', ['typeName' => Str::lower($space->typeName)]) }}
                                </a>
                            </div>

                            <div
                                class="columns is-mobile mb-1 is-hidden-desktop"
                                style="position: fixed; z-index: 9999; bottom: 0; width: 100%;"
                            >
                                <div class="column is-12 p-0">
                                    <div class="notification is-primary m-2 has-text-centered">
                                        <a
                                            href="{{ route('booking.products.show', $space->product->id) }}"
                                            class="button is-white is-outlined"
                                        >
                                            {{ __('Book this :typeName', ['typeName' => Str::lower($space->typeName)]) }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endcan
                        @endif
                    </div>

                    @if ($space->contacts)
                        <h1 class="title is-2 mt-5 mb-2">Contact</h1>

                        <div class="columns is-multiline is-mobile mt-3">
                            @foreach ($space->contacts as $contact)
                                <div class="column is-4-desktop is-6-tablet is-12-mobile">
                                    <div class="card">
                                        <div class="card-content">
                                            <span class="icon-text">
                                                <x-icon icon="fa-user" />
                                                <span>
                                                    : {{ $contact['name'] }}
                                                </span>
                                            </span>

                                            <br>

                                            <span class="icon-text">
                                                <x-icon icon="fa-phone" />
                                                <span>
                                                    : {{ $pageSpace->getPhoneNumberFormat($contact['phone']) }}
                                                </span>
                                            </span>

                                            <br>

                                            <span class="icon-text">
                                                <x-icon icon="fa-envelope" />
                                                <span>
                                                    : {{ $contact['email'] ?? '-' }}
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div id="app-page-space">
                    @if ($hasPitches)
                        <h1 class="title is-2 mt-5 mb-2">{{ __('Pitches') }}</h1>
                        <div class="columns is-multiline is-mobile mt-3 city-pitch-cards">
                            @foreach ($pitches as $spaceChild)
                                <div
                                    class="column is-12-desktop is-12-tablet is-12-mobile py-6 city-pitch-card"
                                    data-pitch-id="{{ $spaceChild->id }}"
                                    data-pitch-name="{{ $spaceChild->name }}"
                                >
                                    <div class="columns is-multiline is-mobile is-hidden-tablet">
                                        <div class="column is-12-desktop is-12-tablet is-12-mobile has-text-centered">
                                            <figure class="image is-250x250 is-inline-block">
                                                <x-image
                                                    src="{{ $spaceChild->getOptimizedLogoImageUrl(250, 250) }}"
                                                    alt="{{ $spaceChild->name }}"
                                                    width="250"
                                                    height="250"
                                                    rounded="is-rounded"
                                                    is-lazyload
                                                />
                                            </figure>
                                        </div>
                                        <div class="column is-12-desktop is-12-tablet is-12-mobile">
                                            <h4 class="title is-4 has-text-primary">
                                                {{ ucwords($spaceChild->name) }}
                                            </h4>
                                            <b>Address: </b>{{ $spaceChild->address ?? '-' }}<br>
                                            <b>Surface: </b>{{ $spaceChild->surface ?? '-' }}<br>
                                            <b>Condition: </b>{{ $spaceChild->condition ?? '-' }}<br>

                                            <h6 class="title is-6 mt-4 mb-1 has-text-primary">
                                                Description
                                            </h6>
                                            <p>
                                                {{ $spaceChild->description ?? '-' }}
                                            </p>

                                            @if ($spaceChild->hasEnabledPage())
                                                <a href="{{ $spaceChild->pageLocalizeURL($translationService->currentLanguage()) }}" class="button is-primary mt-4">Read More</a>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="columns is-hidden-mobile is-multiline is-mobile">
                                        @if ($loop->iteration % 2 == 0)
                                            <div class="column is-4-desktop is-5-tablet is-12-mobile">
                                                <figure class="image is-250x250 is-pulled-left">
                                                    <x-image
                                                        src="{{ $spaceChild->getOptimizedLogoImageUrl(250, 250) }}"
                                                        alt="{{ $spaceChild->name }}"
                                                        width="250"
                                                        height="250"
                                                        rounded="is-rounded"
                                                        is-lazyload
                                                    />
                                                </figure>
                                            </div>
                                        @endif

                                        <div class="column is-8-desktop is-7-tablet is-12-mobile">
                                            <h4 class="title is-4 has-text-primary">
                                                {{ ucwords($spaceChild->name) }}
                                            </h4>
                                            <b>Address: </b>{{ $spaceChild->address ?? '-' }}<br>
                                            <b>Surface: </b>{{ $spaceChild->surface ?? '-' }}<br>
                                            <b>Condition: </b>{{ $spaceChild->condition ?? '-' }}<br>

                                            <h6 class="title is-6 mt-4 mb-1 has-text-primary">
                                                Description
                                            </h6>
                                            <p>
                                                {{ $spaceChild->description ?? '-' }}
                                            </p>

                                            @if ($spaceChild->hasEnabledPage())
                                                <a href="{{ $spaceChild->pageLocalizeURL($translationService->currentLanguage()) }}" class="button is-primary mt-4">Read More</a>
                                            @endif
                                        </div>

                                        @if ($loop->iteration % 2 != 0)
                                            <div class="column is-4-desktop is-5-tablet is-12-mobile">
                                                <figure class="image is-250x250 is-pulled-right">
                                                    <x-image
                                                        src="{{ $spaceChild->getOptimizedLogoImageUrl(250, 250) }}"
                                                        alt="{{ $spaceChild->name }}"
                                                        width="250"
                                                        height="250"
                                                        rounded="is-rounded"
                                                        is-lazyload
                                                    />
                                                </figure>
                                            </div>
                                        @endif

                                        <div class="is-clearfix"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($isCityPage && $hasPitches)
                        <div id="city-pitch-events">
                            <h1
                                class="title is-2 mt-5 mb-2"
                                v-show="selectedPitchId !== null"
                            >
                                {{ __('Upcoming Events') }}
                                <span
                                    v-if="selectedPitchName"
                                    class="is-size-5 has-text-weight-normal"
                                >&mdash; @{{ selectedPitchName }}</span>
                            </h1>

                            <p
                                v-show="selectedPitchId === null"
                                class="has-text-centered my-5 has-text-grey"
                            >
                                {{ __('Select a pitch above to view its upcoming events.') }}
                            </p>

                            <div
                                v-show="selectedPitchId !== null"
                                class="columns is-multiline is-mobile mt-3"
                            >
                                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                                    <space-events
                                        :require-pitch-selection="true"
                                        :selected-space-id="selectedPitchId"
                                        get-record-url="{{ route('api.space.space-events', [ encrypt($space->id) ]) }}"
                                    ></space-events>
                                </div>
                            </div>
                        </div>
                    @elseif (! $hasPitches)
                        <h1 class="title is-2 mt-5 mb-2">{{ __('Upcoming Events') }}</h1>

                        <div class="columns is-multiline is-mobile mt-3">
                            <div class="column is-12-desktop is-12-tablet is-12-mobile">
                                <space-events
                                    get-record-url="{{ route('api.space.space-events', [ encrypt($space->id) ]) }}"
                                ></space-events>
                            </div>
                        </div>
                    @endif
                    </div>{{-- /#app-page-space --}}

                    @if (config('app.debug'))
                        <div class="columns">
                            <div class="column has-text-centered">
                                <p class="is-size-7">
                                    Theme name: <b>{{ $themeName ?? 'Fallback' }}</b>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .city-pitch-card { transition: background-color 150ms ease; border-radius: 6px; }
            .city-pitch-card:hover { background-color: rgba(0, 0, 0, 0.03); }
            .city-pitch-card.is-selected-pitch { background-color: rgba(0, 209, 178, 0.08); box-shadow: inset 0 0 0 2px hsl(171, 100%, 41%); }
        </style>
    @endpush

    @push('scripts')
        @vite('themes/'.config('theme.parent').'/js/page-space.js')
    @endpush
</x-layouts.master>
