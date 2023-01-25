<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;
use Modules\FormBuilder\Http\Controllers\{
    ApiWidgetController,
    FormBuilderController,
    SettingController,
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
        ->name('form-builders.entries')
        ->can('viewAny', 'form_builder');

    Route::get('form-builders/{form_builder}/entries/{entry}', [FormBuilderController::class, 'entryShow'])
        ->name('form-builders.entries.show')
        ->can('view', 'form_builder');

    Route::resource('form-builders', FormBuilderController::class)
        ->except(['show']);

    Route::prefix('form-builders/{form_builder}')
        ->name('form-builders.')
        ->group(function() {
            Route::prefix('settings')->name('settings.')->group(function() {
                Route::get('notifications/records', [SettingNotificationController::class, 'records'])
                    ->name('notifications.records')
                    ->middleware('can:viewAny,Modules\FormBuilder\Entities\FormNotificationSetting');

                Route::resource('notifications', SettingNotificationController::class)
                    ->except(['show']);

                Route::put('general/update', [SettingController::class, 'update'])
                    ->name('general.update')
                    ->can('update', 'form_builder');
            });
        });

    Route::prefix('api')
        ->name('api.')
        ->withoutMiddleware(HandleInertiaRequests::class)
        ->middleware('throttle:api')
        ->group(function() {
            Route::prefix('page-builders')->name('page-builders.')->group(function() {
                Route::get('form-options', [PageBuilderController::class, 'formOptions'])
                    ->name('form-options')
                    ->middleware('can:viewAny,Modules\FormBuilder\Entities\Form');
            });

            Route::get('widget/form-builder/{formBuilder}/entries', [ApiWidgetController::class, 'getEntries'])
                ->name('widget.form-builder.entries');
        });
});

Route::name('form-builders.')->prefix('form-builders')->group(function () {
    Route::get('schema', [FormBuilderController::class, 'getSchema'])
        ->name('schema');

    Route::post('save', [FormBuilderController::class, 'submit'])
        ->middleware([
            'recaptcha',
            'throttle:defaultRequest',
        ])
        ->name('save');
});