@aware(['locale' => ''])

<div @class($entity['id'])>
    <{{ $headingTag }} @class($headingClasses)>{!! $contentHtml !!}</{{ $headingTag }}>
</div>