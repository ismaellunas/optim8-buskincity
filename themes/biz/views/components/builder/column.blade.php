@aware(['entities' => [], 'locale' => '', 'images' => []])

<div
    @class([
        'column',
        'break-long-text',
        $size
    ])
    class="column break-long-text"
>
    @foreach ($components as $pageComponent)
        <x-dynamic-component
            :component="$componentName($pageComponent)"
            :entity="$entities[$pageComponent['id']]"
            :images="$images"
            :locale="$locale"
        />
    @endforeach
</div>
