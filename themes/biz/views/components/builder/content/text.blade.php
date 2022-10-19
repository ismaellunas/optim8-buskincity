<div @class($uniqueClass)>
    <div @class(array_merge(['content'], $classes)) >
        {!! $entityContentHtml !!}
    </div>
</div>