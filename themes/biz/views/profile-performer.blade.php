@inject('userProfile', 'App\Services\UserProfileService')

<x-layouts.master>
    <x-slot name="title">
        Profile
    </x-slot>

    <section
        id="about"
        class="section theme-font"
    >
        <!-- Title -->
        <div class="section-heading has-text-centered">
            <h3 class="title is-2">About Me</h3>
            <h4 class="subtitle is-5">
                {{ $user->fullName }}
            </h4>

            <div class="container">
                <p>
                    {{ $userProfile->getMeta('about_me', $locale) }}
                </p>
            </div>
        </div>

        <div class="columns mt-4">
            @if ($user->profilePhotoUrl)
                <div class="column">
                    <!-- Profile picture -->
                    <div class="card">
                        <div class="card-image">
                            <figure class="image is-4by3">
                                <img src="{{ $user->profilePhotoUrl }}" alt="Placeholder image">
                            </figure>
                        </div>
                    </div>
                </div>
            @endif

            <div class="column">
                <!-- qrcode -->
                @if ($qrCodeIsDisplayed)
                <div class="card">
                    <div class="card-content">
                        <h3 class="title is-4">{{ __('Scan Me') }}</h3>

                        <div class="columns">
                            <div class="column">
                                <p>Scan QR Code to see the performer public page.</p>
                            </div>
                            <div class="column is-one-third">
                                <performer-qr-code
                                    :is-downloaded="true"
                                    qr-code-logo="{{ $qrCodeLogo }}"
                                    qr-code-logo-name="{{ $qrCodeLogoName }}"
                                ></performer-qr-code>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <!-- qrcode -->
                <!-- Profile -->
                <div class="card mt-4">
                    <div class="card-content">
                        <h3 class="title is-4">Profile</h3>

                        <div class="content">
                            <table class="table-profile">
                                <tbody><tr>
                                        <th colspan="1"></th>
                                        <th colspan="2"></th>
                                    </tr>
                                    <tr>
                                        <td>Address:</td>
                                        <td>{{ $userProfile->getMeta('address', $locale) }}</td>
                                    </tr>

                                    @if ($userProfile->getMeta('phone'))
                                        <tr>
                                            <td>Phone:</td>
                                            <td>{{ phone($userProfile->getMeta('phone')['number'], $userProfile->getMeta('phone')['country'], 1) }}</td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <td>Email:</td>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                </tbody></table>
                        </div>
                        <br>
                        <div class="buttons has-addons is-centered">
                            <a
                                href="{{ $userProfile->getMeta('facebook') }}"
                                target="blank"
                                class="button is-link"
                            >
                                Facebook
                            </a>
                            <a
                                href="{{ $userProfile->getMeta('instagram') }}"
                                target="blank"
                                class="button is-link"
                            >
                                Instagram
                            </a>
                        </div>
                    </div>
                </div>
                <!-- END Profile -->

                <!-- Donate -->
                @can ('receiveDonation', $user)
                    <div class="card mt-2">
                        <div class="card-content">
                            <h3 class="title is-4">Donation</h3>

                            <x-stripe-form-donation :user-id="$user->id"/>

                        </div>
                    </div>
                    <!-- END Donate -->
                @endcan
            </div>
        </div>
    </section>
</x-layouts.master>
