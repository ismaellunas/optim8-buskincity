<?php

return [
    'connection' => 'pgsql',

    'table_prefix' => 'lunar_',

    /*
    |--------------------------------------------------------------------------
    | Users Table ID
    |--------------------------------------------------------------------------
    |
    | Lunar adds a relationship to your 'users' table and by default assumes
    | a 'bigint'. You can change this to either an 'int' or 'uuid'.
    |
    */
    'users_id_type' => 'bigint',
    'enable_loading_vendor_migrations' => false,
];
