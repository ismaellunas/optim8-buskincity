@inject('translationService', 'App\Services\TranslationService')

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
                    <div class="column is-12 pt-6 pb-6">
                        <div class="columns is-hidden-tablet">
                            <div class="column is-12 has-text-centered">
                                <figure class="image is-250x250 is-inline-block">
                                    <img src="{{ $space->getOptimizedLogoImageUrl(300, 300) ?? $defaultLogoUrl }}" alt="{{ $space->name }}" class="is-rounded">
                                </figure>
                            </div>
                            <div class="column is-12">
                                <h4 class="title is-4 has-text-primary">
                                    {{ ucwords($space->name) }}
                                </h4>

                                <p>{{ $space->description ?? '-' }}</p>

                                @if ($space->hasEnabledPage())
                                    <a href="{{ $space->pageLocalizeURL($translationService->currentLanguage()) }}" class="button is-primary mt-4">Read More</a>
                                @endif
                            </div>
                        </div>

                        <div class="columns is-hidden-mobile">
                            @if ($loop->iteration % 2 == 0)
                                <div class="column">
                                    <figure class="image is-250x250 is-pulled-left">
                                        <img src="{{ $space->getOptimizedLogoImageUrl(300, 300) ?? $defaultLogoUrl }}" alt="{{ $space->name }}" class="is-rounded">
                                    </figure>
                                </div>
                            @endif

                            <div class="column">
                                <h4 class="title is-4 has-text-primary">
                                    {{ ucwords($space->name) }}
                                </h4>

                                <p>{{ $space->description ?? '-' }}</p>

                                @if ($space->hasEnabledPage())
                                    <a href="{{ $space->pageLocalizeURL($translationService->currentLanguage()) }}" class="button is-primary mt-4">Read More</a>
                                @endif
                            </div>

                            @if ($loop->iteration % 2 != 0)
                                <div class="column">
                                    <figure class="image is-250x250 is-pulled-right">
                                        <img src="{{ $space->getOptimizedLogoImageUrl(300, 300) ?? $defaultLogoUrl }}" alt="{{ $space->name }}" class="is-rounded">
                                    </figure>
                                </div>
                            @endif

                            <div class="is-clearfix"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.master>
