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
        Route::middleware('role:'.config('permission.admin_or_super_admin'))->group(function () {
            Route::get('/settings', 'SettingController@edit')
                ->name('settings.edit');
            Route::post('/settings/update', 'SettingController@update')
                ->name('settings.update');
        });
    });
});
