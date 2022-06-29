@if ($isSectionIncluded)
    <section @class([
        'section',
        'section-'.$uid,
        $sectionSize ?? null,
        $backgroundColor,
    ])>
        <x-builder.row-container/>
    </section>
@else
    <x-builder.row-container/>
@endif
