@aware(['images' => [], 'locale' => null])

<div @class([$uniqueClass, $position])>
    <x-image
        :media="$imageMedia"
        :locale="$locale"
        :ratio="$ratio"
        :rounded="$rounded"
        :square="$fixedSquare"
        :has-position="$hasPosition"
        :style="$imageStyles"
        is-lazyload
    />
</div>