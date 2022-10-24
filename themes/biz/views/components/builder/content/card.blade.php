@aware(['locale' => null])

<div @class($uniqueClass)>
    <div @class([
        'card',
        $cardRounded,
    ])>
        @if ($hasImage)
            <div @class(array_merge(['card-image'], $cardImageClasses))>
                @if ($cardLink)
                    <a href="{{ $cardLink }}">
                        <x-image
                            :media="$imageMedia"
                            :locale="$locale"
                            :ratio="$ratio"
                            :rounded="$rounded"
                            :square="$fixedSquare"
                        />
                    </a>
                @else
                    <x-image
                        :media="$imageMedia"
                        :locale="$locale"
                        :ratio="$ratio"
                        :rounded="$rounded"
                        :square="$fixedSquare"
                    />
                @endif
            </div>
        @endif

        @if ($contentHtml)
            <div class="card-content">
                <div @class(array_merge(['content'], $cardContentClasses))>
                    @if ($cardLink)
                        <a href="{{ $cardLink }}">
                            {!! $contentHtml !!}
                        </a>
                    @else
                        {!! $contentHtml !!}
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>