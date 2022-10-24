@aware([
    'columns' => [],
    'entities' => [],
    'images' => [],
    'isFullwidth' => false,
    'locale' => '',
    'uid' => '',
])

<div @class([
    'container',
    'theme-font',
    'is-fluid' => $isFullwidth,
])>
    <x-builder.columns
        :uid="$uid"
        :columns="$columns"
        :entities="$entities"
        :locale="$locale"
        :images="$images"
    />
</div>
