<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => array_filter([
        'auth:sanctum',
        'verified',
        'can:system.dashboard',
        env('MID_ENSURE_HOME_ENABLED', true) ? 'ensureLoginFromAdminLoginRoute' : null,
    ]),
], function () {
    Route::prefix('booking')->name('booking.')->group(function() {
        Route::middleware('role:'.config('permission.admin_or_super_admin'))->group(function () {
            Route::get('/settings', 'SettingController@edit')
                ->name('settings.edit');
            Route::post('/settings/update', 'SettingController@update')
                ->name('settings.update');
        });
    });
});
