@inject('userProfile', 'App\Services\UserProfileService')
@inject('eventService', 'Modules\Booking\Services\EventService')
@php
    $countryCode = $userProfile->getMeta('country');
    $flagUrl = $countryCode ? url('/images/flags/'.strtolower($countryCode).'.svg') : null;
    $hasPromitionalVideo = (
        $userProfile->getMeta('promotional_video')
        && OEmbed::get($userProfile->getMeta('promotional_video'))
    );
    $hasUploadedGallery = !empty($userProfile->getMeta('gallery'));
@endphp

<x-layouts.master>
    <x-slot name="title">
        {{ $user->fullName . ' | ' .config('app.name') }}
    </x-slot>

    <x-slot name="metaDescription">
        {{ Str::limit($userProfile->getMeta('short_bio', $locale) ?? __('Public profile.'), 155, ' ...') }}
    </x-slot>

    <div class="b752-public-profile section is-small theme-font">
        <div class="container">
            <div class="columns is-multiline is-mobile is-centered">
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
                    <h1 class="title is-2 mt-5 mb-2">{{ $userProfile->getMeta('stage_name') }}</h1>
                    <p class="is-size-7">{{ $userProfile->getMeta('discipline') }}</p>

                    <div class="columns is-multiline is-mobile mt-3">
                        <div class="column is-8-desktop is-12-tablet is-12-mobile">
                            <div class="content has-text-justified">
                                <p>{{ $userProfile->getMeta('short_bio', $locale) }}</p>

                                @if ($userProfile->getMeta('short_bio', $locale) && $userProfile->getMeta('long_bio', $locale))
                                <a href="#" class="has-text-primary has-text-weight-bold js-modal-trigger" data-target="long-bio">{{ __('Read more') }}</a>
                                @endif
                            </div>
                        </div>
                        <div class="column is-4-desktop is-12-tablet is-12-mobile">
                            <div class="buttons is-right">
                                @can ('receiveDonation', $user)
                                    <a href="#" class="button is-primary js-modal-trigger" data-target="donation">
                                        {{ __('Donate') }}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <div id="app-vue">
                        @if (
                            $userProfile->isModuleBookingActivated()
                            && !empty($eventService->getNearestSearchableUpcomingEventByUser($user->id))
                        )
                            <div class="columns is-multiline is-mobile mt-5">
                                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                                    <h2 class="title is-3">
                                        {{ __('Upcoming Events') }}
                                    </h2>
                                </div>
                                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                                    <booking-events
                                        get-record-url="{{ route('api.booking.upcoming-events', $user->unique_key) }}"
                                    ></booking-events>
                                </div>
                            </div>
                        @endif

                        @if ($hasPromitionalVideo || $hasUploadedGallery)
                            <div class="columns is-multiline is-mobile mt-5">
                                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                                    <h2 class="title is-3">
                                        {{ __('Gallery') }}
                                    </h2>
                                </div>

                                @if ($hasPromitionalVideo)
                                    <div class="column is-5-desktop is-12-tablet is-12-mobile">
                                        <figure class="image is-16by9">
                                            {!! OEmbed::get($userProfile->getMeta('promotional_video'))->html(['class' => 'has-ratio']) !!}
                                        </figure>
                                    </div>
                                @endif

                                @if ($hasUploadedGallery)
                                    <div class="column is-7-desktop is-12-tablet is-12-mobile">
                                        <gallery :media="{{ Illuminate\Support\Js::from($userProfile->getMediaWithThumbnails('gallery', 600, 400)) }}">
                                            <template v-slot="{ index, thumbnailUrl, openModal }">
                                                <div class="column is-4-desktop is-6-tablet is-12-mobile">
                                                    <div class="card" @click.prevent="openModal(index)">
                                                        <div class="card-image">
                                                            <figure class="image is-3by2">
                                                                <img
                                                                    :data-src="thumbnailUrl"
                                                                    alt=""
                                                                    class="lazyload"
                                                                    width="480"
                                                                    height="320"
                                                                >
                                                            </figure>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </gallery>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Full performer description is-active -->
    <x-modal id="long-bio">
        <div class="modal-content">
            <div class="card">
                <div class="card-content">
                    <h2 class="title is-4">{{ __('About me') }}</h2>
                    <div class="content has-text-justified">
                        <p>{{ $userProfile->getMeta('long_bio', $locale) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>

    <!-- Modal: Payment donation is-active -->
    @can ('receiveDonation', $user)
    <x-modal id="donation">
        <div class="modal-content is-small">
            <div class="card">
                <div class="card-content">
                    <div class="is-flex">
                        <figure class="profile-picture image is-48x48 mr-3">
                            <x-image
                                src="{{ $user->optimizedProfilePhotoOrDefaultUrl }}"
                                alt="{{ $user->fullName }}"
                                width="48"
                                height="48"
                                rounded="is-rounded"
                                is-lazyload
                            />
                        </figure>
                        <div>
                            <h2 class="title is-5 m-0">{{ __('Donate to') }} {{ $userProfile->getMeta('stage_name') }}</h2>
                            <p>{{ __('Thank you for your support!') }}</p>
                        </div>
                    </div>
                    <x-stripe-form-donation :user-id="$user->id"/>
                </div>
            </div>
        </div>
    </x-modal>
    @endcan

    @push('scripts')
        @vite('themes/'.config('theme.parent').'/js/profile-performer.js')
        @can ('receiveDonation', $user)
        @vite('themes/'.config('theme.parent').'/js/donation.js')
        @endcan
    @endpush
</x-layouts.master>
