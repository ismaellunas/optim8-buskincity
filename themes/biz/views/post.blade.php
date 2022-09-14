<x-layouts.master>
    <x-slot name="title">
        {{ trim($post->meta_title ?? $post->title). ' | ' .config('app.name') }}
    </x-slot>

    <x-slot name="metaDescription">
        {{ $post->meta_description }}
    </x-slot>

    <section class="section theme-font">
        <div
            id="main-container"
            class="container mt-4"
        >
            <h1 class="title is-1 has-text-centered">{{ $post->title }}</h1>
            <div class="content">
                {!! Shortcode::compile($post->content) !!}
            </div>
        </div>
    </section>
</x-layouts.master>
