@php
    $includeString = "@include";
@endphp

@mixin main-text-font()
@isset ($main_text_font)
    #{if(&, '&', '.main-text-font')}
@isset ($main_text_font->family)
        font-family: {{ $main_text_font->family }}
@endisset
        font-weight: {{ config('constants.theme_fonts')['font_weight'][$main_text_font->weight ?? 'has-text-weight-normal'] }}
@if (
    $main_text_font->style === 'is-uppercase'
    || $main_text_font->style === 'is-lowercase'
    || $main_text_font->style === 'is-capitalized')
        text-transform: {{ config('constants.theme_fonts')['font_style'][$main_text_font->style] }}
@elseif ($main_text_font->style === 'is-italic')
        font-style: {{ config('constants.theme_fonts')['font_style'][$main_text_font->style] }}
@elseif ($main_text_font->style === 'is-underlined')
        text-decoration: {{ config('constants.theme_fonts')['font_style'][$main_text_font->style] }}
@endif
@endisset

@mixin button-font()
@isset ($buttons_font)
    #{if(&, '& .button', '.button')}
@isset ($buttons_font->family)
        font-family: {{ $buttons_font->family }}
@endisset
        font-weight: {{ config('constants.theme_fonts')['font_weight'][$buttons_font->weight ?? 'has-text-weight-normal'] }}
@if ($buttons_font->style === 'is-uppercase'
    || $buttons_font->style === 'is-lowercase'
    || $buttons_font->style === 'is-capitalized')
        text-transform: {{ config('constants.theme_fonts')['font_style'][$buttons_font->style] }}
@elseif ($buttons_font->style === 'is-italic')
        font-style: {{ config('constants.theme_fonts')['font_style'][$buttons_font->style] }}
@elseif ($buttons_font->style === 'is-underlined')
        text-decoration: {{ config('constants.theme_fonts')['font_style'][$buttons_font->style] }}
@endif
@endisset

@mixin heading-font()
@isset ($headings_font)
    #{if(&, '&', '.heading-font')}
        h1,
        h2,
        h3,
        h4,
        h5,
        h6
@isset ($headings_font->family)
            font-family: {{ $headings_font->family }}
@endisset
            font-weight: {{ config('constants.theme_fonts')['font_weight'][$headings_font->weight ?? 'has-text-weight-normal'] }}
@if ($headings_font->style === 'is-uppercase'
    || $headings_font->style === 'is-lowercase'
    || $headings_font->style === 'is-capitalized')
            text-transform: {{ config('constants.theme_fonts')['font_style'][$headings_font->style] }}
@elseif ($headings_font->style === 'is-italic')
            font-style: {{ config('constants.theme_fonts')['font_style'][$headings_font->style] }}
@elseif ($headings_font->style === 'is-underlined')
            text-decoration: {{ config('constants.theme_fonts')['font_style'][$headings_font->style] }}
@endif
@endisset

.theme-font
    {{ $includeString }} main-text-font()
    {{ $includeString }} button-font()
    {{ $includeString }} heading-font()

{!! nl2br('') !!}