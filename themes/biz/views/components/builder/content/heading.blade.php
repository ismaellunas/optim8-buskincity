@aware(['locale' => ''])

<div @class($uniqueClass)>
    <{{ $headingTag }} @class($headingClasses)>{!! $contentHtml !!}</{{ $headingTag }}>
</div>