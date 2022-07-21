<x-layouts.master>
    <x-slot name="title">
        {{ trim($metaTitle ?? $space->name). ' | ' .config('app.name') }}
    </x-slot>

    <x-slot name="metaDescription">
        {{ $metaDescription ?? $space->name }}
    </x-slot>

    <section class="section theme-font">
        <div
            id="main-container"
            class="container mt-4"
        >
            <section class="hero is-medium is-info hero-image">
                <div class="hero-body">
                    <div class="columns">
                        <div class="column">
                            <h1 class="title is-size-1">
                                Organize a Lively Event in {{ ucwords($space->name) }}
                            </h1>
                            <p class="subtitle">
                                Are you organizing live entertainment in your city? BuskinCity is the place where all the talented street performers in Europe unite.
                            </p>

                            <a href="#" class="button is-primary">Book Now</a>
                        </div>
                        <div class="column"></div>
                    </div>
                </div>
            </section>

            <div class="columns mt-6">
                <div class="column is-two-fifths">
                    <h3 class="title is-size-3">
                        Find Gifted Street<br>Performers in {{ ucwords($space->name) }}
                    </h3>
                </div>
                <div class="column">
                    <p>
                        <b>I'm in space page (Page: SV)</b><br>
                        The best {{ ucwords($space->name) }} street performers such as {{ ucwords($space->name) }} dancers to {{ ucwords($space->name) }} street musicians come together in BuskinCity looking for a worldwide stage.
                    </p>
                </div>
            </div>

            <div class="columns mt-6">
                <div class="column">
                    <img src="https://res.cloudinary.com/sdb-agency/image/upload/v1658301082/street-art-performers-in-sweden.jpg_ciubgk.webp" alt="performers" style="border-radius: 20px; width: 90%">
                </div>
                <div class="column">
                    <h3 class="title is-size-3">
                        What is {{ ucwords($space->name) }} Best Known for?
                    </h3>
                    <p class="has-text-weight-semibold">
                        Swedish is no doubt the master of pop music. There's a good chance that you've heard {{ ucwords($space->name) }}-created music, even if you can't name a current musician off the top of your head.<br><br>
                        Swedish-written and produced songs, including the top British and American hits such as ABBA, have been at the leader board's top since 1974. Ever since the success of ABBA, the band has opened doors for more Swedish acts, including Roxette, Robyn, Avicii, Lykke Li and much more. They have gained popularity in their music over the years.<br><br>
                        These Swedish street performers also flourished in the country's streets, with famous international street festivals such as the Stockholm Street Festival, which takes place every year since its first edition in 2010. Other shows and performances from Gothenburg's Nightlife, Swedish Design Tour, Gothenburg Instagram Tour are the best places to catch Swedish bands and to find buskers in {{ ucwords($space->name) }}. BuskinCity helps you find the best pop musicians and top street performers in {{ ucwords($space->name) }} to cheer up your event.
                    </p>
                </div>
            </div>

            <img src="https://res.cloudinary.com/sdb-agency/image/upload/v1658301399/culture-and-traditions-1536x485.jpg_vyceps.webp" alt="performers" style="border-radius: 20px">
        </div>
    </section>

    @push('styles')
        <style>
            .hero-image {
                border-radius: 20px;
                background: url('https://res.cloudinary.com/sdb-agency/image/upload/v1658299730/book-street-artists-in-sweden_esvgim.jpg') no-repeat center;
                background-size: cover;
                box-shadow:inset 0 0 0 2000px rgba(0, 0, 0, 0.3);
            }
        </style>
    @endpush
</x-layouts.master>
