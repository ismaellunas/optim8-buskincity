@aware([
    'backgroundColor' => '',
    'hasBackgroundImage',
    'isSectionIncluded',
    'rounded',
    'uniqueClass',
])

<div @class([
        'columns',
        'pb-background-image' => ($hasBackgroundImage && !$isSectionIncluded),
        $uniqueClass,
        $uniqueClass.'-background' => ($hasBackgroundImage && !$isSectionIncluded),
        $backgroundColor => !$isSectionIncluded,
        $rounded
    ])>
    @foreach ($columns as $column)
        <x-builder.column
            :uid="$column['id']"
            :components="$column['components']"
        />
    @endforeach
</div>