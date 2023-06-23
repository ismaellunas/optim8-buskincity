@aware(['locale' => null])

<div @class($uniqueClass)>
    <div @class($cardClasses)>
        @if ($hasImage)
            <div
                @class(array_merge(['card-image', $position], $cardImageClasses))
            >
                @if ($cardLink)
                    <a href="{{ $cardLink }}">
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
                    </a>
                @else
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