@aware([
    'backgroundColor' => '',
    'hasBackgroundImage',
    'isFullwidth',
    'isSectionIncluded',
    'rounded',
    'uniqueClass',
])

@php
    $isBackgroundClassShown = ($hasBackgroundImage && !$isSectionIncluded && !$isFullwidth)
@endphp

<div @class([
        'columns',
        'pb-background-image' => $isBackgroundClassShown,
        $uniqueClass,
        $uniqueClass.'-background' => $isBackgroundClassShown,
        $backgroundColor => (!$isSectionIncluded && !$isFullwidth),
        $rounded
    ])>
    @foreach ($columns as $column)
        <x-builder.column
            :uid="$column['id']"
            :components="$column['components']"
        />
    @endforeach
</div>