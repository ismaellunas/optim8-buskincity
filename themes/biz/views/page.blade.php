<x-layouts.master>
    @push('metas')
        <meta head-key="description"
            name="description"
            content="{{ config('app.name') }}"
        />
    @endpush

    <x-slot name="title">
        {{ $page->title }}
    </x-slot>

    <x-slot name="metaDescription">
        {{ $page->meta_description }}
    </x-slot>

    <div class="page-wrapper py-6">
        @foreach ($page->data->get('structures') as $key => $structure)
            <div @class([
                'container',
                'theme-font',
                'is-fluid' => $page->data->get('entities')[$structure['id']]['config']['wrapper']['isFullwidth'] ?? false,
                $page->data->get('entities')[$structure['id']]['config']['wrapper']['backgroundColor'] ?? '',
            ])>
                <x-builder.columns
                    :uid="$structure['id']"
                    :columns="$structure['columns']"
                    :entities="$page->data->get('entities')"
                    :locale="$currentLanguage"
                    :images="$images"
                />
            </div>
        @endforeach
    </div>

    @push('bottom_styles')
        <x-builder.styles
            :entities="$page->data->get('entities')"
        />
    @endpush
</x-layouts.master>
