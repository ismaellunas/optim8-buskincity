<x-layouts.master>
    <x-slot name="title">
        {{ trim($title) }}
    </x-slot>

    <x-slot name="metaDescription">
        {{ trim(config('app.name')) }}
    </x-slot>

    <section class="hero">
        <figure class="image">
            <img src="https://images.pexels.com/photos/2528985/pexels-photo-2528985.jpeg?auto=compress&amp;cs=tinysrgb&amp;w=1260&amp;h=750&amp;dpr=1" srcset="https://images.pexels.com/photos/2528985/pexels-photo-2528985.jpeg?auto=compress&amp;cs=tinysrgb&amp;w=1260&amp;h=750&amp;dpr=1 1x, https://images.pexels.com/photos/2528985/pexels-photo-2528985.jpeg?auto=compress&amp;cs=tinysrgb&amp;w=1260&amp;h=750&amp;dpr=2 2x" alt="Free Photo of Person Playing Acoustic Guitar Stock Photo" class="spacing_noMargin__Q_PsJ PhotoZoom_image__iR_Ia" width="5989" height="3598" style="transform: none; background: rgb(160, 142, 129);">
        </figure>
    </section>
</x-layouts.master>
