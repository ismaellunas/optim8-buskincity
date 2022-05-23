@php
    $if = '@if';
    $else = '@else';
    $include = "@include";
    $defaultFalse = 'false !default';
    $configFontStyles = config('constants.theme_fonts.font_style');
    $configFontWeights = config('constants.theme_fonts.font_weight');
@endphp

@isset ($main_text_font)
@if ($main_text_font->family)
$biz-body-family: '{{ $main_text_font->family }}', sans-serif
@else
$biz-body-family: {{ $defaultFalse }}
@endif
$biz-body-weight: {{ $configFontWeights[$main_text_font->weight ?? 'has-text-weight-normal'] }}
$biz-body-style: {{ $main_text_font->style ?? $defaultFalse }}
@endisset

@mixin main-text-font()
@isset ($main_text_font)
    #{if(&, '&', '.main-text-font')}
        {{ $if }} $biz-body-family
            font-family: $biz-body-family
        {{ $if }} $biz-body-weight
            font-weight: $biz-body-weight
        .content
            {{ $if }} $biz-body-style == is-uppercase
                text-transform: {{ $configFontStyles['is-uppercase'] }}
            {{ $if }} $biz-body-style == is-lowercase
                text-transform: {{ $configFontStyles['is-lowercase'] }}
            {{ $if }} $biz-body-style == is-capitalized
                text-transform: {{ $configFontStyles['is-capitalized'] }}
            {{ $if }} $biz-body-style == is-italic
                font-style: {{ $configFontStyles['is-italic'] }}
            {{ $if }} $biz-body-style == is-underlined
                text-decoration: {{ $configFontStyles['is-underlined'] }}
@endisset

@isset ($buttons_font)
@if ($buttons_font->family)
$biz-button-family: '{{ $buttons_font->family }}', sans-serif
@else
$biz-button-family: {{ $defaultFalse }}
@endif
$biz-button-weight: {{ $configFontWeights[$buttons_font->weight ?? 'has-text-weight-normal'] }}
$biz-button-style: {{ $buttons_font->style ?? $defaultFalse }}
@endisset

@mixin button-font()
@isset ($buttons_font)
    #{if(&, '& .button', '.button')}
        {{ $if }} $biz-button-family
            font-family: $biz-button-family
        {{ $if }} $biz-button-weight
            font-weight: $biz-button-weight
        {{ $if }} $biz-button-style == is-uppercase
            text-transform: {{ $configFontStyles['is-uppercase'] }}
        {{ $if }} $biz-button-style == is-lowercase
            text-transform: {{ $configFontStyles['is-lowercase'] }}
        {{ $if }} $biz-button-style == is-capitalized
            text-transform: {{ $configFontStyles['is-capitalized'] }}
        {{ $if }} $biz-button-style == is-italic
            font-style: {{ $configFontStyles['is-italic'] }}
        {{ $if }} $biz-button-style == is-underlined
            text-decoration: {{ $configFontStyles['is-underlined'] }}
@endisset

@isset ($headings_font)
@if ($headings_font->family)
$biz-title-family: '{{ $headings_font->family }}', sans-serif
@else
$biz-title-family: {{ $defaultFalse }}
@endif
$biz-title-weight: {{ $configFontWeights[$headings_font->weight ?? 'has-text-weight-normal'] }}
$biz-title-style: {{ $headings_font->style ?? $defaultFalse }}
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
            text-decoration: none
            font-style: normal
            {{ $if }} $biz-title-family
                font-family: $biz-title-family
            {{ $if }} $biz-title-weight
                font-weight: $biz-title-weight
            {{ $if }} $biz-title-style == is-uppercase
                text-transform: {{ $configFontStyles['is-uppercase'] }}
            {{ $if }} $biz-title-style == is-lowercase
                text-transform: {{ $configFontStyles['is-lowercase'] }}
            {{ $if }} $biz-title-style == is-capitalized
                text-transform: {{ $configFontStyles['is-capitalized'] }}
            {{ $if }} $biz-title-style == is-italic
                font-style: {{ $configFontStyles['is-italic'] }}
            {{ $if }} $biz-title-style == is-underlined
                text-decoration: {{ $configFontStyles['is-underlined'] }}
@endisset

.theme-font
    {{ $include }} main-text-font()
.theme-font
    {{ $include }} button-font()
.theme-font
    {{ $include }} heading-font()

{!! nl2br('') !!}