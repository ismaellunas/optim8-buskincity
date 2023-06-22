<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

@if (empty($fontUrls))
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap">
    <link rel="stylesheet" media="print" onload="this.onload=null;this.removeAttribute('media');" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap">
@endif

@foreach ($fontUrls as $fontUrl)
@if (!empty($fontUrl))
    <link rel="preload" as="style" href="{{ $fontUrl }}">
    <link rel="stylesheet" media="print" onload="this.onload=null;this.removeAttribute('media');" href="{{ $fontUrl }}">
    <link rel="stylesheet" href="{{ $fontUrl }}">
@endif
@endforeach

{{-- no-JS fallback --}}
<noscript>
@foreach ($fontUrls as $fontUrl)
    <link rel="stylesheet" href="{{ $fontUrl }}">
@endforeach
</noscript>
