<x-layouts.master>
    @push('metas')
        <meta head-key="description" name="description" content="{{ config('app.name') }}" />
    @endpush

    <x-slot name="title">
        {{ $title }}
    </x-slot>

    <x-slot name="metaDescription">
        Platform Biz 752
    </x-slot>

    <x-slot name="metaKeywords">
        Platform,Biz,752
    </x-slot>

    <section class="hero">
        <figure class="image">
            <img src="https://dummyimage.com/1408x400/808080/fff.png&text=B752" alt="hero-image">
        </figure>
    </section>

    <section class="section theme-font">
        <div class="container">
            Hello there
        </div>
    </section>

    <section class="section theme-font">
        <div class="container">
            <div class="notification is-info">
                <button class="delete"></button>
                Theme Default
        </div>
    </section>
</x-layouts.master>
