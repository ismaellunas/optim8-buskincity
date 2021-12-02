<figure @class(array_merge(['image'], $figureClasses)) >
    <img
        @class($imageClasses)
        @if ($alt) alt="{{ $alt }}" @endif
        @if ($src) src="{{ $src }}" @endif
        @if ($style) style="{{ $style }}" @endif
    >
    {{ $slot }}
</figure>