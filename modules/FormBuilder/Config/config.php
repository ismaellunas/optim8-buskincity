<?php

return [
    'name' => 'FormBuilder',

    'permissions' => [
        'form_builder.*',
        'form_builder.browse',
        'form_builder.read',
        'form_builder.edit',
        'form_builder.add',
        'form_builder.delete',
        'form_builder.automate_user_creation',
    ],

    // Email
    'default_from_email' => 'noreply@'.config('constants.domain'),
];
