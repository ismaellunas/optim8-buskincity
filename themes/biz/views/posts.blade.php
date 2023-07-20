<x-layouts.master>
    <x-slot name="title">
        {{ $metaTitle . ' | ' .config('app.name') }}
    </x-slot>

    @if ($metaDescription)
        <x-slot name="metaDescription">
            {{ $metaDescription }}
        </x-slot>
    @endif

    <section class="section theme-font">
        <div id="main-container" class="container">
            <div class="columns is-multiline is-mobile">
                <div class="column is-4-desktop is-6-tablet is-12-mobile is-offset-8-desktop is-offset-6-tablet">
                    <form action="{{ $searchRoute }}" method="get">
                        <div class="field is-horizontal">
                            <div class="field-body">
                                <div class="field">
                                    <div class="field has-addons">
                                        <div class="control is-expanded">
                                            <input
                                                class="input"
                                                type="text"
                                                placeholder="{{ __('Search') }}..."
                                                maxlength="255"
                                                name="term"
                                                value="{{ request('term') }}"
                                            >
                                        </div>
                                        <div class="control">
                                            <button class="button">
                                                <x-icon icon="fa-search" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="columns is-multiline is-mobile">
                @foreach ($records as $record)
                    <x-post-item
                        :post="$record"
                        :link="route('blog.show', [$record->slug])"
                    />
                @endforeach
            </div>

            <x-pagination
                :paginator="$records"
                :query-params="$pageQueryParams"
            />
        </div>
    </section>

    @push('scripts')
        @vite('themes/'.config('theme.active').'/js/app.js')
    @endpush
</x-layouts.master>
