<div @class($uniqueClass)>
    <div class="contents">
        <div
            @class(['content', $config['alignment']])
        >
            <span
                @class(['icon', 'pb-icon-'.$id, $config['color'] ?? ''])
            >
                <i @class($config['class'])></i>
            </span>
        </div>
    </div>
</div>