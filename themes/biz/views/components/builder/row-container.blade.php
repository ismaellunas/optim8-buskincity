@aware([
    'backgroundColor' => '',
    'columns' => [],
    'configColumns' => [],
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
    $isBackgroundColorShown = (!$isSectionIncluded && $isFullwidth);
    $isBackgroundImageShown = ($hasBackgroundImage && !$isSectionIncluded && $isFullwidth);
@endphp

<div @class([
    'container',
    'is-fluid' => $isFullwidth,
    'pb-background-image' => $isBackgroundImageShown,
    'theme-font',
    $backgroundColor => $isBackgroundColorShown,
    $uniqueClass.'-background' => $isBackgroundImageShown,

])>
    <x-builder.columns
        :uid="$uid"
        :columns="$columns"
        :entities="$entities"
        :locale="$locale"
        :images="$images"
        :config="$configColumns"
    />
</div>
