<figure {{ $attributes->merge(['class' => "image ".implode(' ', $figureClasses)]) }}>
    <img
        @class($imageClasses)
        @if ($alt) alt="{{ $alt }}" @endif
        @if ($src) src="{{ $src }}" @endif
        @if ($style) style="{{ $style }}" @endif
    >
    {{ $slot }}
</figure>