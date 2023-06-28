@aware(['images' => [], 'locale' => null])

<div @class([$uniqueClass, $position])>
    <x-figure-image
        :media="$imageMedia"
        :locale="$locale"
        :ratio="$ratio"
        :rounded="$rounded"
        :square="$fixedSquare"
        :has-position="$hasPosition"
        :img-style="$imageStyles"
        is-lazyload
    />
</div>