<carousel
    @class($entity['id'])
    :config="{{ Illuminate\Support\Js::from($config) }}"
    :entity-images="{{ Illuminate\Support\Js::from($carouselImages) }}"
    :slide-speed="{{ $slideSpeed }}"
/>