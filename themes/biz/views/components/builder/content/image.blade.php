@aware(['images' => [], 'locale' => null])

<div @class($uniqueClass)>
    <x-image
        :media="$imageMedia"
        :locale="$locale"
        :ratio="$ratio"
        :rounded="$rounded"
        :square="$fixedSquare"
    />
</div>