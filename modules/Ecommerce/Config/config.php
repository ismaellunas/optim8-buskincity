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
];
