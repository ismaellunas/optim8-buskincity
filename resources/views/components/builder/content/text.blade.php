@aware(['locale' => ''])

<div {{ $attributes->class($wrapperClasses) }} >
    <div @class(array_merge(['content'], $classes)) >
        {!! $entity['content']['html'] !!}
    </div>
</div>