<div @class($uniqueClass)>
    @if ($embed)
        <figure class="image is-16by9">
            {!! $embed->html(['class' => 'has-ratio']) !!}
        </figure>
    @else
        <div class="hero is-medium is-primary is-radius">
            <div class="hero-body"></div>
        </div>
    @endif
</div>