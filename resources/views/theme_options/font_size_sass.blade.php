@php
    $media = "@media";

    $desktop = "1024px";
    $tablet = "769px";
@endphp

.theme-font
  {{ $media }} screen and (min-width: {{ $desktop }})
    .title.is-1
      font-size: {{ $font_size_heading_1['desktop'] }}em
    .title.is-2
      font-size: {{ $font_size_heading_2['desktop'] }}em
    .title.is-3
      font-size: {{ $font_size_heading_3['desktop'] }}em
    .title.is-4
      font-size: {{ $font_size_heading_4['desktop'] }}em
    .title.is-5
      font-size: {{ $font_size_heading_5['desktop'] }}em
    .title.is-6
      font-size: {{ $font_size_heading_6['desktop'] }}em
    .content
      font-size: {{ $font_size_text['desktop'] }}em
      h1
        font-size: {{ $font_size_heading_1['desktop'] }}em
      h2
        font-size: {{ $font_size_heading_2['desktop'] }}em
      h3
        font-size: {{ $font_size_heading_3['desktop'] }}em
      h4
        font-size: {{ $font_size_heading_4['desktop'] }}em
      h5
        font-size: {{ $font_size_heading_5['desktop'] }}em
      h6
        font-size: {{ $font_size_heading_6['desktop'] }}em
    .content.is-small
      font-size: {{ $font_size_small['desktop'] }}em
    .breadcrumb, .label
      font-size: {{ $font_size_text['desktop'] }}em
    .input, .textarea
      font-size: {{ $font_size_text['desktop'] }}em
    .select select
      font-size: {{ $font_size_text['desktop'] }}em
    .control
      .button, .icon
        font-size: {{ $font_size_text['desktop'] }}em

  {{ $media }} screen and (min-width: {{ $tablet }}) and (max-width: {{ $desktop }} - 1px)
    .title.is-1
      font-size: {{ $font_size_heading_1['tablet'] }}em
    .title.is-2
      font-size: {{ $font_size_heading_2['tablet'] }}em
    .title.is-3
      font-size: {{ $font_size_heading_3['tablet'] }}em
    .title.is-4
      font-size: {{ $font_size_heading_4['tablet'] }}em
    .title.is-5
      font-size: {{ $font_size_heading_5['tablet'] }}em
    .title.is-6
      font-size: {{ $font_size_heading_6['tablet'] }}em
    .content
      font-size: {{ $font_size_text['tablet'] }}em
      h1
        font-size: {{ $font_size_heading_1['tablet'] }}em
      h2
        font-size: {{ $font_size_heading_2['tablet'] }}em
      h3
        font-size: {{ $font_size_heading_3['tablet'] }}em
      h4
        font-size: {{ $font_size_heading_4['tablet'] }}em
      h5
        font-size: {{ $font_size_heading_5['tablet'] }}em
      h6
        font-size: {{ $font_size_heading_6['tablet'] }}em
    .content.is-small
      font-size: {{ $font_size_small['tablet'] }}em
    .breadcrumb, .label
      font-size: {{ $font_size_text['tablet'] }}em
    .input, .textarea
      font-size: {{ $font_size_text['tablet'] }}em
    .select select
      font-size: {{ $font_size_text['tablet'] }}em
    .control
      .button, .icon
        font-size: {{ $font_size_text['tablet'] }}em

  {{ $media }} screen and (max-width: {{ $tablet }} - 1px)
    .title.is-1
      font-size: {{ $font_size_heading_1['mobile'] }}em
    .title.is-2
      font-size: {{ $font_size_heading_2['mobile'] }}em
    .title.is-3
      font-size: {{ $font_size_heading_3['mobile'] }}em
    .title.is-4
      font-size: {{ $font_size_heading_4['mobile'] }}em
    .title.is-5
      font-size: {{ $font_size_heading_5['mobile'] }}em
    .title.is-6
      font-size: {{ $font_size_heading_6['mobile'] }}em
    .content
      font-size: {{ $font_size_text['mobile'] }}em
      h1
        font-size: {{ $font_size_heading_1['mobile'] }}em
      h2
        font-size: {{ $font_size_heading_2['mobile'] }}em
      h3
        font-size: {{ $font_size_heading_3['mobile'] }}em
      h4
        font-size: {{ $font_size_heading_4['mobile'] }}em
      h5
        font-size: {{ $font_size_heading_5['mobile'] }}em
      h6
        font-size: {{ $font_size_heading_6['mobile'] }}em
    .content.is-small
      font-size: {{ $font_size_small['mobile'] }}em
    .breadcrumb, .label
      font-size: {{ $font_size_text['mobile'] }}em
    .input, .textarea
      font-size: {{ $font_size_text['mobile'] }}em
    .select select
      font-size: {{ $font_size_text['mobile'] }}em
    .control
      .button, .icon
        font-size: {{ $font_size_text['mobile'] }}em

{!! nl2br('') !!}
