<div @class($entity['id'])>
    <div class="contents">
        <div
            @class(['content', $config['alignment']])
        >
            <span
                @class(['icon', 'icon-'.$uid])
            >
                <i @class($config['class'])></i>
            </span>
        </div>
    </div>
</div>