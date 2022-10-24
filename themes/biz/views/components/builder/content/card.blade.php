@aware(['locale' => null])

<div @class($uniqueClass)>
    <div @class([
        'card',
        $cardRounded,
    ])>
        @if ($hasImage)
            <div @class(array_merge(['card-image'], $cardImageClasses))>
                <x-image
                    :media="$imageMedia"
                    :locale="$locale"
                    :ratio="$ratio"
                    :rounded="$rounded"
                    :square="$fixedSquare"
                />
            </div>
        @endif

        @if ($contentHtml)
            <div class="card-content">
                <div @class(array_merge(['content'], $cardContentClasses)) >
                    {!! $contentHtml !!}
                </div>
            </div>
        @endif
    </div>
</div>