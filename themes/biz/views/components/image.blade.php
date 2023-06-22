<img
    @class($imageClasses)
    @if ($alt) alt="{{ $alt }}" @endif
    @if ($src)
        @if (! $isLazyload) src="{{ $src }}" @else data-src="{{ $src }}" @endif
    @endif
    @if ($style) style="{{ $style }}" @endif
>