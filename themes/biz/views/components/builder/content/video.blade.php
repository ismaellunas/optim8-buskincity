<div @class($uniqueClass)>
    @if (
        $url
        && OEmbed::get($url)
    )
        <figure class="image is-16by9">
            {!! OEmbed::get($url)->html(['class' => 'has-ratio']) !!}
        </figure>
    @else
        <div class="hero is-medium is-primary is-radius">
            <div class="hero-body"></div>
        </div>
    @endif
</div>