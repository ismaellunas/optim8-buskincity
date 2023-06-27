<?php

return [
    'domain' => env('APP_DOMAIN', 'biz752.com'),

    'fontawesome_local' => env('FONTAWESOME_LOCAL', false),

    'error_reporting' => env('ERROR_REPORTING', false),

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
            'is-capitalized' => 'capitalize',
            'is-uppercase' => 'uppercase',
            'is-lowercase' => 'lowercase',
            'is-italic' => 'italic',
            'is-underlined' => 'underline',
        ],
    ],
    'theme_font_sizes' => [
        'font_size_heading_1' => [
            'desktop' => 48,
            'tablet' => 28,
            'mobile' => 24,
        ],
        'font_size_heading_2' => [
            'desktop' => 32,
            'tablet' => 22,
            'mobile' => 20,
        ],
        'font_size_heading_3' => [
            'desktop' => 28,
            'tablet' => 16,
            'mobile' => 14,
        ],
        'font_size_heading_4' => [
            'desktop' => 24,
            'tablet' => 14,
            'mobile' => 12,
        ],
        'font_size_heading_5' => [
            'desktop' => 20,
            'tablet' => 12,
            'mobile' => 10,
        ],
        'font_size_heading_6' => [
            'desktop' => 16,
            'tablet' => 10,
            'mobile' => 8,
        ],
        'font_size_text' => [
            'desktop' => 16,
            'tablet' => 14,
            'mobile' => 12,
        ],
        'font_size_small' => [
            'desktop' => 12,
            'tablet' => 10,
            'mobile' => 8,
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

    'theme_error_page' => [
        401,
        403,
        404,
        429,
        500,
        503
    ],

    'locale' => [
        'en',
        'sv',
        'es',
        'de'
    ],

    'translations' => [
        'groups' => [
            'no_group' => 'No Group',
            'auth' => 'Auth',
            'pagination' => 'Pagination',
            'passwords' => 'Password',
            'validation' => 'Validation',
            'routes' => 'Routes',
        ]
    ],
    'setting_cache' => [
        'default_locale' => 'default_locale',
        'locale_options' => 'locale_options',
        'shown_language_option' => 'shown_language_option',
        'supported_languages' => 'supported_languages',
    ],

    'throttle' => [
        'default' => 5,
        'checkout' => 100,
    ],

    'format' => [
        'date_time' => 'Y/m/d H:i:s',
        'date_time_minute' => 'Y/m/d H:i',
        'date_time_event' => 'Y/m/d H:i (\G\M\T P)',
        'date_post' => 'd F Y',
        'time_checkin' => 'H:i (\G\M\T P)',
    ],

    'currency_symbols' => [
        'SEK' => 'SEK',
        'EUR' => '&euro;',
        'GBP' => '&pound;',
        'USD' => '&dollar;',
    ],

    'profile_photo_path' => '/images/profile-picture-default.jpg',
    'cover_path' => '/images/cover-default.jpg',

    'max_length' => [
        'meta_description' => 160,
        'meta_title' => 80,
        'excerpt' => '800',
    ],

    'max_words' => [
        'excerpt' => 60,
    ],

    'settings' => [
        'keys' => [
            [
                'key' => 'google_api_key',
                'display_name' => 'Google Map API',
                'value' => null,
                'order' => 1,
                'group' => 'key.google_api'
            ],
            [
                'key' => 'recaptcha_site_key',
                'display_name' => 'Google Recaptcha Site',
                'value' => null,
                'order' => 2,
                'group' => 'key.google_recaptcha'
            ],
            [
                'key' => 'recaptcha_secret_key',
                'display_name' => 'Google Recaptcha Secret',
                'value' => null,
                'order' => 3,
                'group' => 'key.google_recaptcha'
            ],
            [
                'key' => 'google_client_id',
                'display_name' => 'OAuth Google Client ID',
                'value' => null,
                'order' => 4,
                'group' => 'key.oauth_google'
            ],
            [
                'key' => 'google_client_secret',
                'display_name' => 'OAuth Google Client Secret',
                'value' => null,
                'order' => 5,
                'group' => 'key.oauth_google'
            ],
            [
                'key' => 'facebook_client_id',
                'display_name' => 'OAuth Facebook Client ID',
                'value' => null,
                'order' => 6,
                'group' => 'key.oauth_facebook'
            ],
            [
                'key' => 'facebook_client_secret',
                'display_name' => 'OAuth Facebook Client Secret',
                'value' => null,
                'order' => 7,
                'group' => 'key.oauth_facebook'
            ],
            [
                'key' => 'twitter_client_id',
                'display_name' => 'OAuth Twitter Client ID',
                'value' => null,
                'order' => 8,
                'group' => 'key.oauth_twitter'
            ],
            [
                'key' => 'twitter_client_secret',
                'display_name' => 'OAuth Twitter Client Secret',
                'value' => null,
                'order' => 9,
                'group' => 'key.oauth_twitter'
            ],
            [
                'key' => 'ipregistry_api_key',
                'display_name' => 'IPRegistry API',
                'value' => null,
                'order' => 10,
                'group' => 'key.ipregistry_api'
            ],
            [
                'key' => 'stripe_pk',
                'display_name' => 'Stripe PK',
                'value' => null,
                'order' => 11,
                'group' => 'key.stripe'
            ],
            [
                'key' => 'stripe_sk',
                'display_name' => 'Stripe SK',
                'value' => null,
                'order' => 12,
                'group' => 'key.stripe'
            ],
            [
                'key' => 'stripe_endpoint_secret',
                'display_name' => 'Stripe Endpoint Secret',
                'value' => null,
                'order' => 13,
                'group' => 'key.stripe'
            ],
            [
                'key' => 'tinymce_api_key',
                'display_name' => 'TinyMCE API',
                'value' => null,
                'order' => 14,
                'group' => 'key.tinymce'
            ],
        ],

        'recaptcha' => [
            'score' => 0.6,
        ]
    ],

    'reading_time_per_minute' => 200,

    'regex' => [
        // @see https://www.regextester.com/96461
        // @see https://stackoverflow.com/questions/5612602/improving-regex-for-parsing-youtube-vimeo-urls
        'youtube_vimeo_url' => '^(http:\/\/|https:\/\/)(vimeo\.com|youtu\.be|www\.youtube\.com|player\.vimeo\.com)\/((video\/|embed\/|watch\?v=|v\/)|[\w\/\S]+)([\?]\S*)?$',
    ],

    'default_images' => [
        'logo' => 'default-logo.png',
        'logo_space' => 'default-square.png',
        'widget_post_thumbnail' => 'default-square.png',
        'pb_latest_post' => 'default-square.png',
        'pb_video' => 'default-video.png',
        'admin_auth_card' => 'default-auth-card.png',
        'user_auth_card' => 'default-auth-card.png',
        'post_thumbnail' => 'default-post-thumbnail.png',
    ],

    'file_size' => [
        'profile_picture' => 3 * 1024,
    ],

    'untranslated_routes' => [
        'login',
        'register',
        'admin.login',
    ],

    'max_file_size' => 10 * 1024,

    'recomended_dimensions' => [
        'favicon' => '180 x 180px',
        'logo' => '300 x 300px',
        'profile_picture' => '600 x 600px',
        'cover' => '2560 x 576px',
        'gallery' => '1280 x 720px',
    ],

    'dimensions' => [
        'profile_picture' => [
            'width' => 300,
            'height' => 300,
        ],
        'logo' => [
            'width' => 300,
            'height' => 300,
        ],
        'gallery' => [
            'width' => 1200,
            'height' => 800,
        ],
    ],

    'icon' => [
        'type' => 'fa-light',
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe Payment Gateway
    |--------------------------------------------------------------------------
    */

    'stripe_payment_method_types' => [
        'card',
        'acss_debit',
        'afterpay_clearpay',
        'alipay',
        'au_becs_debit',
        'bacs_debit',
        'bancontact',
        'boleto',
        'eps',
        'fpx',
        'giropay',
        'grabpay',
        'ideal',
        'klarna',
        'oxxo',
        'p24',
        'sepa_debit',
        'sofort',
        'wechat_pay',
    ],

    'stripe_fee_percent' => 0.02,

    'stripe_payment_currencies' => [
        'SEK',
        'EUR',
        'GBP',
        'USD',
    ],

    'stripe_minimal_payments' => [
        'USD' => 0.50,
        'AED' => 2.00,
        'AUD' => 0.50,
        'BGN' => 1.00,
        'BRL' => 0.50,
        'CAD' => 0.50,
        'CHF' => 0.50,
        'CZK' => 15.00,
        'DKK' => 2.50,
        'EUR' => 0.50,
        'GBP' => 0.30,
        'HKD' => 4.00,
        'HUF' => 175.00,
        'INR' => 0.50,
        'JPY' => 50,
        'MXN' => 10,
        'MYR' => 2,
        'NOK' => 3.00,
        'NZD' => 0.50,
        'PLN' => 2.00,
        'RON' => 2.00,
        'SEK' => 3.00,
        'SGD' => 0.50,
    ],
];
