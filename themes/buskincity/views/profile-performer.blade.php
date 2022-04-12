@inject('userProfile', 'App\Services\UserProfileService')
@inject('country', 'App\Services\CountryService')

<x-layouts.master>
    <x-slot name="title">
        Profile
    </x-slot>

    <section id="about" class="section theme-font">
        <!-- Title -->
        <div class="section-heading has-text-centered">
            <h3 class="title is-2">{{ __('About Me') }}</h3>
        </div>

        <div class="columns mt-4">
            <div class="column">
                <div class="card">
                    @if ($userProfile->getMedias('top_background_picture')->first())
                    <div class="card-image">
                        <figure class="image is-3by1">
                            <img src="{{ $userProfile->getMedias('top_background_picture')->first()->file_url }}" alt="Top background image">
                        </figure>
                    </div>
                    @endif

                    <div class="card-content">
                        <div class="media">
                            @if ($user->profilePhotoUrl)
                            <div class="media-left">
                                <!-- Profile picture -->
                                <figure class="image is-128x128">
                                    <img src="{{ $user->profilePhotoUrl }}" alt="Profile image">
                                </figure>
                            </div>
                            @endif

                            <div class="media-content">
                                <p class="title is-4">
                                    {{ $user->fullName }}
                                </p>
                                <p class="subtitle is-6">
                                    {{ $user->email }}
                                </p>
                            </div>
                        </div>

                        <div class="content">
                            <p>
                                {{ $userProfile->getMeta('short_bio', $locale) }}
                            </p>

                            @if ($userProfile->getMeta('promotional_video'))
                            <h3 class="title is-5">{{ __('Promotional Video') }}</h3>
                            <iframe width="100%" height="300"
                                src="{{ $userProfile->getMeta('promotional_video') }}"
                                frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                            </iframe>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="column">
                <!-- Profile -->
                <div class="card">
                    <div class="card-content">
                        <h3 class="title is-4">{{ __('Profile') }}</h3>

                        <div class="content">
                            <table class="table-profile">
                                <tbody>
                                    <tr>
                                        <th colspan="1"></th>
                                        <th colspan="2"></th>
                                    </tr>
                                    @if ($userProfile->getMeta('address'))
                                    <tr>
                                        <td>{{ __('Address') }}:</td>
                                        <td>{{ $userProfile->getMeta('address', $locale).", ".$userProfile->getMeta('city').", ".$userProfile->getMeta('postcode').", ".$country->getCountryName($userProfile->getMeta('country')) }}</td>
                                    </tr>
                                    @endif

                                    @if ($userProfile->getMeta('phone'))
                                    <tr>
                                        <td>{{ __('Phone') }}:</td>
                                        <td>{{ phone($userProfile->getMeta('phone')['number'], $userProfile->getMeta('phone')['country'], 1) }}</td>
                                    </tr>
                                    @endif

                                    <tr>
                                        <td>{{ __('Discipline') }}:</td>
                                        <td>{{ $userProfile->getMeta('discipline') }}</td>
                                    </tr>

                                    <tr>
                                        <td>{{ __('Stage Name') }}:</td>
                                        <td>{{ $userProfile->getMeta('stage_name') }}</td>
                                    </tr>

                                    <tr>
                                        <td>{{ __('Bio') }}:</td>
                                        <td>{{ $userProfile->getMeta('long_bio', $locale) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <br>
                        <div class="buttons has-addons is-centered">
                            @if ($userProfile->getMeta('facebook'))
                            <a href="{{ $userProfile->getMeta('facebook') }}" target="blank" class="button is-link">
                                Facebook
                            </a>
                            @endif

                            @if ($userProfile->getMeta('twitter'))
                            <a href="{{ $userProfile->getMeta('twitter') }}" target="blank" class="button is-link">
                                Twitter
                            </a>
                            @endif

                            @if ($userProfile->getMeta('instagram'))
                            <a href="{{ $userProfile->getMeta('instagram') }}" target="blank" class="button is-link">
                                Instagram
                            </a>
                            @endif

                            @if ($userProfile->getMeta('youtube'))
                            <a href="{{ $userProfile->getMeta('youtube') }}" target="blank" class="button is-link">
                                Youtube
                            </a>
                            @endif

                            @if ($userProfile->getMeta('tiktok'))
                            <a href="{{ $userProfile->getMeta('tiktok') }}" target="blank" class="button is-link">
                                Tiktok
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- END Profile -->

                <!-- Donate -->
                @can ('receiveDonation', $user)
                <div class="card mt-2">
                    <div class="card-content">
                        <h3 class="title is-4">Donation</h3>

                        <x-stripe-form-donation :user-id="$user->id" />

                    </div>
                </div>
                <!-- END Donate -->
                @endcan
            </div>
        </div>

        @if ($userProfile->getMedias('gallery')->count() > 0)
        <div class="columns">
            <div class="column">
                <div class="card mt-2">
                    <div class="card-content">
                        <h3 class="title is-4">{{ __('Gallery') }}</h3>

                        <div class="columns is-multiline">
                            @foreach ($userProfile->getMedias('gallery') as $gallery)
                            <div class="column is-4">
                                <div class="card">
                                    <div class="card-image has-text-centered">
                                        @if ($gallery['file_type'] == 'image')
                                        <figure class="image is-4by3">
                                            <img src="{{ $gallery['file_url'] }}" alt="Placeholder image">
                                        </figure>
                                        @else
                                        <video width="640" height="480" controls>
                                            <source src="{{ $gallery['file_url'] }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </section>
</x-layouts.master>