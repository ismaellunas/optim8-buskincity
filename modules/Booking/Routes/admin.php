<?php

use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'verified',
    'can:system.dashboard',
    'ensureLoginFromAdminLoginRoute',
])->group(function () {
    Route::prefix('booking')->name('booking.')->group(function() {
        Route::get('/settings', 'SettingController@edit');

        Route::post('/settings/update', 'SettingController@update')
            ->name('settings.update');
    });
});
