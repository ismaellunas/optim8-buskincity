<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => [
        'auth:sanctum',
        'verified',
        'can:system.dashboard',
        'ensureLoginFromAdminLoginRoute',
    ],
], function () {
    Route::prefix('booking')->name('booking.')->group(function() {
        Route::middleware('role:Super Administrator|Administrator')->group(function () {
            Route::get('/settings', 'SettingController@edit')
                ->name('settings.edit');
            Route::post('/settings/update', 'SettingController@update')
                ->name('settings.update');
        });
    });
});
