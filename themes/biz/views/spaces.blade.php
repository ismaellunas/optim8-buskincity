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
            <div class="columns is-multiline is-mobile">
                @foreach ($spaces as $space)
                    <div class="column is-12-desktop is-12-tablet is-12-mobile pt-4 pb-6">
                        <div class="columns is-hidden-tablet is-multiline is-mobile">
                            <div class="column is-12-desktop is-12-tablet is-12-mobile has-text-centered">
                                <figure class="image is-250x250 is-inline-block">
                                    <x-image
                                        src="{{ $space->getOptimizedLogoImageUrl(250, 250) ?? $defaultLogoUrl }}"
                                        alt="{{ $space->name }}"
                                        width="250"
                                        height="250"
                                        rounded="is-rounded"
                                        is-lazyload
                                    />
                                </figure>
                            </div>
                            <div class="column is-12-desktop is-12-tablet is-12-mobile has-text-justified">
                                <h4 class="title is-4 has-text-primary">
                                    {{ ucwords($space->name) }}
                                </h4>

                                <p>{{ $space->description ?? '-' }}</p>

                                @if ($space->hasEnabledPage())
                                    <a href="{{ $space->pageLocalizeURL($translationService->currentLanguage()) }}" class="button is-primary mt-4">Read More</a>
                                @endif
                            </div>
                        </div>

                        <div class="columns is-hidden-mobile is-multiline is-mobile">
                            @if ($loop->iteration % 2 == 0)
                                <div class="column is-4-desktop is-5-tablet is-12-mobile">
                                    <figure class="image is-250x250 is-pulled-left">
                                        <x-image
                                            src="{{ $space->getOptimizedLogoImageUrl(250, 250) ?? $defaultLogoUrl }}"
                                            alt="{{ $space->name }}"
                                            width="250"
                                            height="250"
                                            rounded="is-rounded"
                                            is-lazyload
                                        />
                                    </figure>
                                </div>
                            @endif

                            <div class="column is-8-desktop is-7-tablet is-12-mobile has-text-justified">
                                <h4 class="title is-4 has-text-primary">
                                    {{ ucwords($space->name) }}
                                </h4>

                                <p>{{ $space->description ?? '-' }}</p>

                                @if ($space->hasEnabledPage())
                                    <a href="{{ $space->pageLocalizeURL($translationService->currentLanguage()) }}" class="button is-primary mt-4">Read More</a>
                                @endif
                            </div>

                            @if ($loop->iteration % 2 != 0)
                                <div class="column is-4-desktop is-5-tablet is-12-mobile">
                                    <figure class="image is-250x250 is-pulled-right">
                                        <x-image
                                            src="{{ $space->getOptimizedLogoImageUrl(250, 250) ?? $defaultLogoUrl }}"
                                            alt="{{ $space->name }}"
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
        </div>
    </section>
</x-layouts.master>
