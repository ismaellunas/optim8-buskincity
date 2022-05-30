@inject('userProfile', 'App\Services\UserProfileService')

<x-layouts.master>
    <x-slot name="title">
        Profile
    </x-slot>

    <div class="b752-public-profile section is-small">
        <div class="container">
            <div class="columns is-multiline is-centered">
                <div class="column is-12">
                    <div class="profile-background hero is-medium is-primary is-radius" style="background-image: url({{ $userProfile->getMedias('top_background_picture')->first()->file_url ?? null }});">
                        <div class="hero-body"></div>
                    </div>
                </div>
                <div class="column is-11">
                    <figure class="profile-picture image is-250x250">
                        <img src="{{ $user->profilePhotoUrl ?? url('/images/profile-picture-default.png') }}" alt="{{ $user->fullName }}" class="is-rounded">

                        @php
                            $countryCode = $userProfile->getMeta('country');
                            $flagUrl = $countryCode ? url('/images/flags/'.strtolower($countryCode).'.svg') : null;
                        @endphp
                        @if ($flagUrl)
                        <span class="flag">
                            <img src="{{ $flagUrl }}" alt="Portugal" class="is-rounded">
                        </span>
                        @endif
                    </figure>

                    <h1 class="title is-2 mt-5 mb-2">{{ $userProfile->getMeta('stage_name') }}</h1>
                    <p class="is-size-7">{{ $userProfile->getMeta('discipline') }}</p>

                    <div class="columns is-multiline mt-3">
                        <div class="column is-8">
                            <div class="content">
                                <p>{{ $userProfile->getMeta('short_bio', $locale) }}</p>

                                @if ($userProfile->getMeta('short_bio', $locale) && $userProfile->getMeta('long_bio', $locale))
                                <a href="#" class="has-text-primary has-text-weight-bold" onclick="openModal('long-bio')">{{ __('Read more') }}</a>
                                @endif
                            </div>
                        </div>
                        <div class="column is-4">
                            <div class="buttons is-right">
                                @if ($userProfile->getMeta('tiktok'))
                                <a href="{{ $userProfile->getMeta('tiktok') }}" class="button">
                                    <span class="icon is-small">
                                        <i class="fa-brands fa-tiktok"></i>
                                    </span>
                                </a>
                                @endif

                                @if ($userProfile->getMeta('youtube'))
                                <a href="{{ $userProfile->getMeta('youtube') }}" class="button">
                                    <span class="icon is-small">
                                        <i class="fa-brands fa-youtube"></i>
                                    </span>
                                </a>
                                @endif

                                @if ($userProfile->getMeta('instagram'))
                                <a href="{{ $userProfile->getMeta('instagram') }}" class="button">
                                    <span class="icon is-small">
                                        <i class="fa-brands fa-instagram"></i>
                                    </span>
                                </a>
                                @endif

                                @if ($userProfile->getMeta('facebook'))
                                <a href="{{ $userProfile->getMeta('facebook') }}" class="button">
                                    <span class="icon is-small">
                                        <i class="fa-brands fa-facebook"></i>
                                    </span>
                                </a>
                                @endif

                                @can ('receiveDonation', $user)
                                <a href="#" class="button is-primary" onclick="openModal('donation')">Donate</a>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <div class="columns is-multiline mt-5">
                        <div class="column is-12">
                            <h2 class="title is-3">Gallery</h2>
                        </div>
                        <div class="column is-5">
                            <div class="hero is-medium is-primary is-radius">
                                <div class="hero-body">
                                    
                                </div>
                            </div>
                        </div>

                        <div class="column is-7">
                            <div class="hero is-medium is-primary is-radius">
                                <div class="hero-body">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Full performer description is-active -->
    <x-modal id="long-bio">
        <h2 class="title is-4">{{ __('About me') }}</h2>
        <div class="content">
            <p>{{ $userProfile->getMeta('long_bio', $locale) }}</p>
        </div>
    </x-modal>

    <!-- Modal: Payment donation is-active -->
    @can ('receiveDonation', $user)
    <x-modal id="donation" modalContentClass="is-small">
        <div class="is-flex">
            <figure class="profile-picture image is-48x48 mr-3">
                <img src="{{ $user->profilePhotoUrl ?? url('/images/profile-picture-default.png') }}" alt="{{ $user->fullName }}" class="is-rounded">
            </figure>
            <div>
                <h2 class="title is-5 m-0">{{ __('Donate to') }} {{ $userProfile->getMeta('stage_name') }}</h2>
                <p>{{ __('Thank you for your support!') }}</p>
            </div>
        </div>

        <x-stripe-form-donation :user-id="$user->id"/>
    </x-modal>
    @endcan

    @push('bottom_scripts')
    <script>
        function openModal(id) {
            let modal = document.getElementById(id);

            modal.classList.add('is-active');
        }
    </script>
    @endpush
</x-layouts.master>