<div @class($uniqueClass)>
    <div @class($cardClasses)>
        <div class="card-content">
            <div @class(array_merge(['content'], $cardContentClasses)) >
                {!! $contentHtml !!}
            </div>
        </div>
    </div>
</div>