@inject('pageSpace', 'Modules\Space\Services\PageSpaceService')
@inject('translationService', 'App\Services\TranslationService')

<x-layouts.master>
    <x-slot name="title">
        {{ trim($metaTitle ?? $space->name). ' | ' .config('app.name') }}
    </x-slot>

    <x-slot name="metaDescription">
        {{ $metaDescription ?? $space->name }}
    </x-slot>

    <div class="b752-public-profile section is-small">
        <div class="container">
            <div class="columns is-multiline is-centered">
                <div class="column is-12">
                    <div
                        class="profile-background hero is-medium is-primary is-radius"
                        @if ($space->coverUrl) style="background-image: url({{ $space->getOptimizedCoverImageUrl(1280, 720) }});" @endif
                    >
                        <div class="hero-body"></div>
                    </div>
                </div>
                <div class="column is-11">
                    <figure class="profile-picture image is-250x250">
                        <img
                            src="{{ $space->getOptimizedLogoImageUrl(300, 300) ?? $pageSpace->defaultLogoUrl() }}"
                            alt="{{ $space->name }}"
                            class="is-rounded">
                    </figure>

                    <h1 class="title is-2 mt-5 mb-2">{{ ucwords($space->name) }}</h1>
                    <p class="is-size-7">{{ $space->address }}</p>

                    <div class="columns is-multiline mt-3">
                        <div class="column">
                            <div class="content">
                                <p>{{ $space->description }}</p>
                            </div>
                        </div>
                    </div>

                    <h1 class="title is-2 mt-5 mb-2">Contact</h1>

                    <div class="columns is-multiline mt-3">
                        @if ($space->contacts)
                            @foreach ($space->contacts as $contact)
                                <div class="column is-4">
                                    <div class="card">
                                        <div class="card-content">
                                            <i class="fa-solid fa-user"></i> : {{ $contact['name'] }}<br>
                                            <i class="fa-solid fa-phone"></i> : {{ $pageSpace->getPhoneNumberFormat($contact['phone']) }}<br>
                                            <i class="fa-solid fa-envelope"></i> : {{ $contact['email'] ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="column">
                                <p>No contact</p>
                            </div>
                        @endif
                    </div>

                    <h1 class="title is-2 mt-5 mb-2">Events</h1>

                    <div class="columns mt-3">
                        <div class="column">
                            <table class="table is-bordered is-fullwidth">
                                <tr>
                                    <th>Event</th>
                                    <th>Started At</th>
                                    <th>Ended At</th>
                                    <th>Address</th>
                                </tr>
                                @if ($space->events()->exists())
                                    @foreach ($space->events as $event)
                                        <tr>
                                            <td>
                                                <b>{{ $event->title }}</b><br>
                                                {{ $event->description ?? null }}
                                            </td>
                                            <td>
                                                {{ $pageSpace->eventDateTimeFormat($event->started_at) }}
                                            </td>
                                            <td>
                                                {{ $pageSpace->eventDateTimeFormat($event->ended_at) }}
                                            </td>
                                            <td>{{ $event->address }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                <tr>
                                    <td colspan="4" class="has-text-centered">
                                        {{ __('No event') }}
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <div class="columns is-multiline mt-3">
                        @foreach ($pageSpace->getLeaves() as $spaceChild)
                            <div class="column is-12 pt-6 pb-6">
                                <div class="columns is-hidden-tablet">
                                    <div class="column has-text-centered">
                                        <figure class="image is-250x250 is-inline-block">
                                            <img src="{{ $spaceChild->logoUrl ?? $pageSpace->defaultLogoUrl() }}" alt="{{ $spaceChild->name }}" class="is-rounded">
                                        </figure>
                                    </div>
                                    <div class="column">
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

                                <div class="columns is-hidden-mobile">
                                    @if ($loop->iteration % 2 == 0)
                                        <div class="column">
                                            <figure class="image is-250x250 is-pulled-left">
                                                <img src="{{ $spaceChild->logoUrl ?? $pageSpace->defaultLogoUrl() }}" alt="{{ $spaceChild->name }}" class="is-rounded">
                                            </figure>
                                        </div>
                                    @endif

                                    <div class="column">
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
                                        <div class="column">
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
