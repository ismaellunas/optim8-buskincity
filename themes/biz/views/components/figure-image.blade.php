<figure
    @class(array_merge(
        ['image is-clipped'],
        $figureClasses
    ))
    style="{{ $style }}"
>
    <x-image
        :media="$media"
        :src="$src"
        :alt="$alt"
        :locale="$locale"
        :rounded="$rounded"
        :style="$imgStyle"
        :is-lazyload="$isLazyload"
    />
</figure>