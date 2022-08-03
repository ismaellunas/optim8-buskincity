<x-layouts.master>
    <x-slot name="title">
        {{ $metaTitle }}
    </x-slot>

    @if ($metaDescription)
        <x-slot name="metaDescription">
            {{ $metaDescription }}
        </x-slot>
    @endif

    <section class="section theme-font">
        <div
            id="main-container"
            class="container"
        >
            <div class="columns is-multiline mt-3">
                @foreach ($spaces as $space)
                    @if ($loop->iteration % 2 == 0)
                        <div class="column is-12 pt-6 pb-6">
                            <div class="columns">
                                <div class="column">
                                    <figure class="image is-250x250 is-pulled-left">
                                        <img src="{{ $space->logoUrl ?? $defaultLogoUrl }}" alt="{{ $space->name }}" class="is-rounded">
                                    </figure>
                                </div>
                                <div class="column">
                                    <h4 class="title is-4 has-text-primary">
                                        {{ ucwords($space->name) }}
                                    </h4>

                                    <p>
                                        {{ $space->description ?? '-' }}
                                    </p>
                                    <a href="{{ $space->landingPageUrl }}" class="button is-primary mt-4">Read More</a>

                                </div>
                            </div>
                        </div>
                    @else
                        <div class="column is-12 pt-6 pb-6">
                            <div class="columns">
                                <div class="column">
                                    <h4 class="title is-4 has-text-primary">
                                        {{ ucwords($space->name) }}
                                    </h4>

                                    <p>
                                        {{ $space->description ?? '-' }}
                                    </p>
                                    <a href="{{ $space->landingPageUrl }}" class="button is-primary mt-4">Read More</a>
                                </div>
                                <div class="column">
                                    <figure class="image is-250x250 is-pulled-right">
                                        <img src="{{ $space->logoUrl ?? $defaultLogoUrl }}" alt="{{ $space->name }}" class="is-rounded">
                                    </figure>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.master>
