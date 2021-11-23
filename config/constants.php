<?php

return [
    'google_api_key' => env('GOOGLE_API_KEY'),

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
    ],

    'theme_colors' => [
        'color_primary' => '#00d1b2',
        'color_link' => '#485fc7',
        'color_info' => '#3e8ed0',
        'color_success' => '#48c78e',
        'color_warning' => '#ffe08a',
        'color_danger' => '#f14668',
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
    'theme_additional_code_files' => [
        'tracking_code_inside_head' => [
            'key' => 'tracking_code_inside_head_url',
            'filename' => 'tracking_code_inside_head.css',
        ],
        'tracking_code_after_body' => [
            'key' => 'tracking_code_after_body_url',
            'filename' => 'tracking_code_after_body.css',
        ],
        'tracking_code_before_body' => [
            'key' => 'tracking_code_before_body_url',
            'filename' => 'tracking_code_before_body.css',
        ],
        'additional_css' => [
            'key' => 'additional_css_url',
            'filename' => 'additional_css.css',
        ],
        'additional_javascript' => [
            'key' => 'additional_javascript_url',
            'filename' => 'additional_javascript.js',
        ],
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
];
