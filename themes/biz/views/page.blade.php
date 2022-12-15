@inject('pageBuilderService', 'App\Services\PageBuilderService')

<x-layouts.master
    :has-header="!$page->isLayoutNoHeaderAndFooter"
    :has-footer="!$page->isLayoutNoHeaderAndFooter"
    :body-classes="$pageBuilderService->bodyClasses($page)"
    :body-styles="$pageBuilderService->bodyStyles($page)"
>
    <x-slot name="title">
        {{ trim($page->meta_title ?? $page->title).' | '.config('app.name') }}
    </x-slot>

    <x-slot name="metaDescription">
        {{ trim($page->meta_description ?? "") }}
    </x-slot>

    <div @class([
        'page-wrapper' => true,
        'py-6' => !$page->isLayoutNoHeaderAndFooter,
    ])>
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
