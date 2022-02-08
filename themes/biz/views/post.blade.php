<x-layouts.master>
    @push('metas')
        <meta head-key="description"
            name="description"
            content="{{ $post->meta_description }}"
        />
    @endpush

    <x-slot name="title">
        {{ $post->meta_title }}
    </x-slot>

    <x-slot name="metaDescription">
        {{ $post->meta_description }}
    </x-slot>

    <section class="section">
        <div
            id="main-container"
            class="container mt-4 main-content"
        >
            <h1 class="title is-1 has-text-centered">{{ $post->title }}</h1>
            <div class="content">
                {!! $post->content !!}
            </div>
        </div>
    </section>
</x-layouts.master>
