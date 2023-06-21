@inject('pageSpace', 'Modules\Space\Services\PageSpaceService')
@inject('translationService', 'App\Services\TranslationService')
@php
    $leaves = $pageSpace->getLeaves();
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
                        @if ($space->coverUrl) style="background-image: url({{ $space->getOptimizedCoverImageUrl(1280, 720) }});" @endif
                    >
                        <div class="hero-body">
                            <div class="title">&nbsp;</div>
                            <div class="subtitle">&nbsp;</div>
                        </div>
                    </div>
                </div>

                <div class="column is-11-desktop is-11-tablet is-11-mobile">
                    <figure class="profile-picture image is-250x250">
                        <img
                            src="{{ $space->getOptimizedLogoImageUrl(300, 300) ?? $pageSpace->defaultLogoUrl() }}"
                            alt="{{ $space->name }}"
                            class="is-rounded">
                    </figure>
                </div>

                <div class="column is-11-desktop is-12-tablet is-12-mobile">
                    <h1 class="title is-2 mt-5 mb-2">{{ ucwords($space->name) }}</h1>

                    <p class="is-size-7">{{ $space->address }}</p>

                    <div class="columns is-multiline is-mobile mt-3">
                        <div class="column is-12-desktop is-12-tablet is-12-mobile">
                            <div class="content has-text-justified">
                                <p>{{ $space->description }}</p>
                            </div>
                        </div>
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

                    <h1 class="title is-2 mt-5 mb-2">{{ __('Upcoming Events') }}</h1>

                    <div class="columns is-multiline is-mobile mt-3">
                        <div class="column is-12-desktop is-12-tablet is-12-mobile">
                            <space-events
                                get-record-url="{{ route('api.space.space-events', [ encrypt($space->id) ]) }}"
                            ></space-events>
                        </div>
                    </div>

                    @if ($leaves)
                    <div class="columns is-multiline is-mobile mt-3">
                        @foreach ($leaves as $spaceChild)
                            <div class="column is-12-desktop is-12-tablet is-12-mobile py-6">
                                <div class="columns is-multiline is-mobile is-hidden-tablet">
                                    <div class="column is-12-desktop is-12-tablet is-12-mobile has-text-centered">
                                        <figure class="image is-250x250 is-inline-block">
                                            <img src="{{ $spaceChild->logoUrl ?? $pageSpace->defaultLogoUrl() }}" alt="{{ $spaceChild->name }}" class="is-rounded">
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
                                                <img src="{{ $spaceChild->logoUrl ?? $pageSpace->defaultLogoUrl() }}" alt="{{ $spaceChild->name }}" class="is-rounded">
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
                                                <img src="{{ $spaceChild->logoUrl ?? $pageSpace->defaultLogoUrl() }}" alt="{{ $spaceChild->name }}" class="is-rounded">
                                            </figure>
                                        </div>
                                    @endif

                                    <div class="is-clearfix"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif

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
</x-layouts.master>
