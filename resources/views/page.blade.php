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

    <section class="section">
        <div class="container">
            @foreach ($page->data->get('structures') as $key => $structure)
                <x-builder.columns
                    :uid="$structure['id']"
                    :columns="$structure['columns']"
                    :entities="$page->data->get('entities')"
                    :locale="$currentLanguage"
                    :images="$images"
                />
            @endforeach
        </div>
    </section>
</x-layouts.master>
