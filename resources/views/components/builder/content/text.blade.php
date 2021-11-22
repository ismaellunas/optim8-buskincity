@aware(['locale' => ''])

<div {{ $attributes->class($wrapperClasses) }} >
    <div class="{{ implode(' ', $classes) }}">
        {!! $entity['content']['html'] !!}
    </div>
</div>