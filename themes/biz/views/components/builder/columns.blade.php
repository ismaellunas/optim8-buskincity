@aware([
    'backgroundColor' => '',
    'hasBackgroundImage',
    'isFullwidth',
    'isSectionIncluded',
    'rounded',
    'uniqueClass',
])

@php
    $isBackgroundColorShown = (!$isSectionIncluded && !$isFullwidth);
    $isBackgroundImageShown = ($hasBackgroundImage && !$isSectionIncluded && !$isFullwidth);
@endphp

<div @class([
        'columns',
        'pb-background-image' => $isBackgroundImageShown,
        $uniqueClass,
        $uniqueClass.'-background' => $isBackgroundImageShown,
        $backgroundColor => $isBackgroundColorShown,
        $rounded
    ])>
    @foreach ($columns as $column)
        <x-builder.column
            :uid="$column['id']"
            :components="$column['components']"
        />
    @endforeach
</div>