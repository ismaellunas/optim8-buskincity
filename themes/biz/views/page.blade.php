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
            <x-builder.row
                :uid="$structure['id']"
                :columns="$structure['columns']"
                :entities="$page->data->get('entities')"
                :locale="$currentLanguage"
                :images="$images"
            />
        @endforeach
    </div>

    @push('bottom_styles')
        <link rel="stylesheet" href="{{ $page->styleUrl }}">
    @endpush
</x-layouts.master>
