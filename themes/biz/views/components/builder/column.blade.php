@aware(['entities' => [], 'locale' => '', 'images' => []])

<div class="column break-long-text">
    @foreach ($components as $pageComponent)
        <x-dynamic-component
            :component="$componentName($pageComponent['componentName'])"
            :entity="$entities[$pageComponent['id']]"
            :images="$images"
            :locale="$locale"
        />
    @endforeach
</div>