<div @class($entity['id'])>
    <div @class(array_merge(['content'], $classes)) >
        {!! $entity['content']['html'] !!}
    </div>
</div>