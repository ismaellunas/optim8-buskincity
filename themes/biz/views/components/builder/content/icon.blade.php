<div class="contents">
    <div
        @class(['content', $config['alignment']])
    >
        <span
            {{ $attributes->merge(['id' => $uid, 'class' => 'icon']) }}
        >
            <i @class($config['class'])></i>
        </span>
    </div>
</div>