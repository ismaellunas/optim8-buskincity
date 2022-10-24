<div @class($uniqueClass)>
    <div @class([
        'card',
        $cardRounded,
    ])>
        <div class="card-content">
            <div @class(array_merge(['content'], $cardContentClasses)) >
                {!! $contentHtml !!}
            </div>
        </div>
    </div>
</div>