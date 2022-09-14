<?php

use Illuminate\Support\Facades\Route;
use Modules\FormBuilder\Http\Controllers\{
    FormBuilderController,
    SettingNotificationController,
    PageBuilderController,
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::name('admin.')->prefix('admin/')->middleware([
    'auth:sanctum',
    'verified',
    'can:system.dashboard',
    'ensureLoginFromAdminLoginRoute',
])->group(function () {
    Route::get('form-builders/{form_builder}/entries', [FormBuilderController::class, 'entries'])
        ->name('form-builders.entries');
    Route::resource('form-builders', FormBuilderController::class)
        ->except(['show']);

    Route::prefix('form-builders/{form_builder}')->name('form-builders.')->group(function() {
        Route::prefix('settings')->name('settings.')->group(function() {
            Route::get('notifications/records', [SettingNotificationController::class, 'records'])
                ->name('notifications.records');

            Route::resource('notifications', SettingNotificationController::class)
                ->except(['show']);
        });
    });

    Route::prefix('api')->name('api.')->group(function() {
        Route::prefix('page-builders')->name('page-builders.')->group(function() {
            Route::get('form-options', [PageBuilderController::class, 'formOptions'])
                ->name('form-options');
        });
    });
});

Route::name('form-builders.')->prefix('form-builders')->group(function () {
    Route::get('schema', [FormBuilderController::class, 'getSchema'])
        ->name('schema');

    Route::post('save', [FormBuilderController::class, 'submit'])
        ->middleware(['recaptcha'])
        ->name('save');
});