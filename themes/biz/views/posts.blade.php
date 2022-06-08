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
            <div class="columns">
                <div class="column">
                    <form action="{{ $searchRoute }}" method="get">
                        <div class="field is-horizontal">
                            <div class="field-label is-normal">
                                <label class="label">
                                    {{ __('Search') }}
                                </label>
                            </div>
                            <div class="field-body">
                                <div class="field">
                                    <div class="control">
                                        <div class="columns">
                                            <div class="column is-three-quarters">
                                                <input
                                                    class="input"
                                                    type="text"
                                                    placeholder="{{ __('Search') }}..."
                                                    maxlength="255"
                                                    name="term"
                                                    value="{{ request('term') }}"
                                                >
                                            </div>
                                            <div class="column">
                                                <button class="button">
                                                    <span class="icon" >
                                                        <i class="fas fa-search"></i>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-container">
                <table class="table is-striped is-hoverable is-fullwidth">
                    <tbody>
                        @foreach ($records as $record)
                            <x-post-list-item
                                :post="$record"
                                :link="route('blog.show', [$record->slug])"
                            >
                            </x-post-list-item>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination
                :paginator="$records"
                :query-params="$pageQueryParams"
            />
        </div>
    </section>
</x-layouts.master>
