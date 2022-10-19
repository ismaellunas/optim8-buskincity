@aware([
    'backgroundColor' => '',
    'isSectionIncluded',
    'uniqueClass',
    'hasBackgroundImage',
])

<div @class([
        'columns',
        'pb-background-image' => ($hasBackgroundImage && !$isSectionIncluded),
        $uniqueClass,
        $uniqueClass.'-background' => ($hasBackgroundImage && !$isSectionIncluded),
        $backgroundColor => !$isSectionIncluded
    ])>
    @foreach ($columns as $column)
        <x-builder.column
            :uid="$column['id']"
            :components="$column['components']"
        />
    @endforeach
</div>