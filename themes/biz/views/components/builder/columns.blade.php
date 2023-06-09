@aware([
    'customId' => '',
    'backgroundColor' => '',
    'hasBackgroundImage',
    'isFullwidth',
    'isSectionIncluded',
    'rounded',
    'uniqueClass',
    'configColumns',
])

@php
    $isBackgroundColorShown = (!$isSectionIncluded && !$isFullwidth);
    $isBackgroundImageShown = ($hasBackgroundImage && !$isSectionIncluded && !$isFullwidth);
@endphp

<div id="{{ $customId }}" @class([
        'columns',
        'is-multiline',
        'is-mobile',
        'pb-background-image' => $isBackgroundImageShown,
        $uniqueClass,
        $uniqueClass.'-background' => $isBackgroundImageShown,
        $backgroundColor => $isBackgroundColorShown,
        $rounded,
        'is-centered' => $configColumns['isCentered'],
    ])>
    @foreach ($columns as $column)
        <x-builder.column
            :uid="$column['id']"
            :components="$column['components']"
            :size="$getSize($loop->index)"
        />
    @endforeach
</div>
