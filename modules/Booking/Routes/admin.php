<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'booking',
    'name' => 'booking.',
    'middleware' => [
        'auth:sanctum',
        'verified',
        'can:system.dashboard',
        'ensureLoginFromAdminLoginRoute',
    ],
], function () {
    Route::middleware('role:Super Administrator|Administrator')->group(function() {
        Route::get('/settings', 'SettingController@edit');
        Route::post('/settings/update', 'SettingController@update')
            ->name('settings.update');
    });
});
