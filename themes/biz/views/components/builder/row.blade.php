@if ($isSectionIncluded)
    <section @class([
        'section',
        'section-'.$uid,
        $sectionSize ?? null,
        $backgroundColor,
        $uniqueClass.'-background' => $hasBackgroundImage,
        'pb-background-image' => $hasBackgroundImage,
    ])>
        <x-builder.row-container/>
    </section>
@else
    <x-builder.row-container/>
@endif
