@aware(['images' => [], 'locale' => null])

<div @class($entity['id'])>
    <x-image
        :media="$imageMedia"
        :locale="$locale"
        :ratio="$ratio"
        :rounded="$rounded"
        :square="$fixedSquare"
    />
</div>