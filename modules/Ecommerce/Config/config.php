<?php

return [
    'name' => 'Ecommerce',

    'permissions' => [
        'product.*',
        'product.browse',
        'product.read',
        'product.edit',
        'product.add',
        'product.delete',

        'order.*',
        'order.browse',
        'order.read',
        'order.edit',
        'order.add',
        'order.delete',
    ],

    'format' => [
        'date_event_email_title' => 'D, j M Y',
        'date_event_email_body' => 'H:i - l, j F Y (e)',
        'date_event_widget_record' => 'H:i - D, j M Y (e)',
    ],
];
