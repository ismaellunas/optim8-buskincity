@aware([
    'customId' => '',
    'backgroundColor' => '',
    'hasBackgroundImage',
    'isFullwidth',
    'isSectionIncluded',
    'rounded',
    'uniqueClass',
    'sizeColumns',
])

@php
    $isBackgroundColorShown = (!$isSectionIncluded && !$isFullwidth);
    $isBackgroundImageShown = ($hasBackgroundImage && !$isSectionIncluded && !$isFullwidth);
@endphp

<div id="{{ $customId }}" @class([
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
            :size="$getSize($sizeColumns, $loop->index)"
        />
    @endforeach
</div>