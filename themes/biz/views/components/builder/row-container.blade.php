@aware([
    'backgroundColor' => '',
    'columns' => [],
    'entities' => [],
    'hasBackgroundImage',
    'images' => [],
    'isFullwidth' => false,
    'isSectionIncluded',
    'locale' => '',
    'uid' => '',
    'uniqueClass',
])

@php
    $isBackgroundClassShown = ($hasBackgroundImage && !$isSectionIncluded && $isFullwidth)
@endphp

<div @class([
    'container',
    'is-fluid' => $isFullwidth,
    'pb-background-image' => $isBackgroundClassShown,
    'theme-font',
    $backgroundColor => (!$isSectionIncluded && $isFullwidth),
    $uniqueClass.'-background' => $isBackgroundClassShown,

])>
    <x-builder.columns
        :uid="$uid"
        :columns="$columns"
        :entities="$entities"
        :locale="$locale"
        :images="$images"
    />
</div>
