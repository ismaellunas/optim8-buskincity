@isset ($main_text_font)
*
    font-family: {{ $main_text_font->family }}
    font-weight: {{ config('constants.theme_fonts')['font_weight'][$main_text_font->weight] }}
@if ($main_text_font->style === 'is-uppercase'
    || $main_text_font->style === 'is-lowercase'
    || $main_text_font->style === 'is-capitalized')
    text-transform: {{ config('constants.theme_fonts')['font_style'][$main_text_font->style] }}
@elseif ($main_text_font->style === 'is-italic')
    font-style: {{ config('constants.theme_fonts')['font_style'][$main_text_font->style] }}
@else
    text-decoration: {{ config('constants.theme_fonts')['font_style'][$main_text_font->style] }}
@endif
@endisset

@isset ($buttons_font)
.button
    font-family: {{ $buttons_font->family }}
    font-weight: {{ config('constants.theme_fonts')['font_weight'][$buttons_font->weight] }}
@if ($buttons_font->style === 'is-uppercase'
    || $buttons_font->style === 'is-lowercase'
    || $buttons_font->style === 'is-capitalized')
    text-transform: {{ config('constants.theme_fonts')['font_style'][$buttons_font->style] }}
@elseif ($buttons_font->style === 'is-italic')
    font-style: {{ config('constants.theme_fonts')['font_style'][$buttons_font->style] }}
@else
    text-decoration: {{ config('constants.theme_fonts')['font_style'][$buttons_font->style] }}
@endif
@endisset

@isset ($headings_font)
h1,
h2,
h3,
h4,
h5,
h6
    font-family: {{ $headings_font->family }}
    font-weight: {{ config('constants.theme_fonts')['font_weight'][$headings_font->weight] }}
@if ($headings_font->style === 'is-uppercase'
    || $headings_font->style === 'is-lowercase'
    || $headings_font->style === 'is-capitalized')
    text-transform: {{ config('constants.theme_fonts')['font_style'][$headings_font->style] }}
@elseif ($headings_font->style === 'is-italic')
    font-style: {{ config('constants.theme_fonts')['font_style'][$headings_font->style] }}
@else
    text-decoration: {{ config('constants.theme_fonts')['font_style'][$headings_font->style] }}
@endif
@endisset