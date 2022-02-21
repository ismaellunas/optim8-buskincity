<?php

return [
    'google_api_key' => env('GOOGLE_API_KEY'),

    'fontawesome_local' => env('FONTAWESOME_LOCAL', false),

    'one_megabyte' => 1024,
    'extensions' => [
        'image' => [
            'jpeg',
            'jpg',
            'png',
            'gif',
        ],
        'video' => [
            'mp4',
            'mov',
            'avi',
            'mpg',
            'ogv',
            '3gp',
        ],
        'document' => [
            'pdf',
            'doc',
            'docx',
        ],
        'spreadsheet' => [
            'xls',
            'xlsx',
        ],
        'presentation' => [
            'ppt',
            'pptx',
        ],
        'import' => [
            'csv',
        ],
    ],

    'theme_colors' => [
        'color_primary' => '#00d1b2',
        'color_link' => '#485fc7',
        'color_info' => '#3e8ed0',
        'color_success' => '#48c78e',
        'color_warning' => '#ffe08a',
        'color_danger' => '#f14668',
    ],
    'theme_fonts' => [
        'font_weight' => [
            'has-text-weight-light' => 300,
            'has-text-weight-normal' => 400,
            'has-text-weight-medium' => 500,
            'has-text-weight-semibold' => 600,
            'has-text-weight-bold' => 700,
        ],
        'font_style' => [
            'is-capitalize' => 'capitalize',
            'is-uppercase' => 'uppercase',
            'is-lowercase' => 'lowercase',
            'is-italic' => 'italic',
            'is-underlined' => 'underline',
        ],
    ],
    'theme_font_sizes' => [
        'font_size_heading_1' => 2,
        'font_size_heading_2' => 1.75,
        'font_size_heading_3' => 1.5,
        'font_size_heading_4' => 1.25,
        'font_size_heading_5' => 1.125,
        'font_size_heading_6' => 1,
        'font_size_text' => 1,
        'font_size_small' => 0.75,
    ],
    'theme_uppercases' => [
        'site_title',
        'main_navigation',
        'h_elements',
        'section_titles',
        'site_description',
        'buttons'
    ],
    'theme_content_paragraph_width' => 1440,
    'theme_header' => [
        'header_logo_media' => [
            'key' => 'header_logo_media_id',
        ],
    ],
    'locale' => [
        'en',
        'sv',
        'es',
        'de'
    ],

    'translations' => [
        'groups' => [
            'auth',
            'pagination',
            'passwords',
            'validation',
        ]
    ],
    'setting_cache' => [
        'locale_options' => 'locale_options',
        'default_locale' => 'default_locale',
        'shown_language_option' => 'shown_language_option',
    ],
    'widget_cache' => [
        'post' => 'widget_post',
        'user' => 'widget_user',
    ],
];
