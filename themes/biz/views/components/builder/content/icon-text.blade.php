<div @class($uniqueClass)>
    <div @class(array_merge(['content'], $classes)) >
        <span class="icon-text">
            <span class="icon">
                <i @class($iconClass)></i>
            </span>
            <span>
                {{ $entityContentText }}
            </span>
        </span>
    </div>
</div>