<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;
use Modules\FormBuilder\Http\Controllers\{
    ApiWidgetController,
    AutomateUserCreationController,
    FormBuilderController,
    FormEntryController,
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
    Route::get('form-builders/{form_builder}/entries', [FormEntryController::class, 'index'])
        ->name('form-builders.entries.index')
        ->can('viewAny', 'form_builder');

    Route::get('form-builders/{form_builder}/entries/{form_entry}', [FormEntryController::class, 'show'])
        ->name('form-builders.entries.show')
        ->withTrashed()
        ->can('view', 'form_builder');

    Route::resource('form-builders', FormBuilderController::class)
        ->except(['show']);

    Route::post(
        'form-builders/{form_builder}/automate-user-creation/mapped-fields',
        [AutomateUserCreationController::class, 'save']
    )
        ->name('form-builders.automate-user-creation.mapped-fields.save');

    Route::post(
        'form-builders/{form_builder}/entries/{form_entry}/create-or-update-user',
        [AutomateUserCreationController::class, 'createOrUpdateUser']
    )
        ->name('form-builders.entries.automate-user-creation.create-or-update')
        ->can('automateUserCreation', 'form_entry');

    Route::name('form-builders.entries.')->prefix('form-builders/{form_builder}/entries/')->group(function () {
        Route::post('bulk-mark-as-read', [FormEntryController::class, 'bulkMarkAsRead'])
            ->name('bulk-mark-as-read');

        Route::post('bulk-mark-as-unread', [FormEntryController::class, 'bulkMarkAsUnread'])
            ->name('bulk-mark-as-unread');

        Route::post('bulk-archive', [FormEntryController::class, 'bulkArchive'])
            ->name('bulk-archive');

        Route::post('bulk-restore', [FormEntryController::class, 'bulkRestore'])
            ->name('bulk-restore');

        Route::post('bulk-force-delete', [FormEntryController::class, 'bulkForceDelete'])
            ->name('bulk-force-delete');

        Route::post('mark-as-read/{form_entry}', [FormEntryController::class, 'markAsRead'])
            ->name('mark-as-read');

        Route::post('mark-as-unread/{form_entry}', [FormEntryController::class, 'markAsUnread'])
            ->name('mark-as-unread');

        Route::post('archive/{form_entry}', [FormEntryController::class, 'archive'])
            ->name('archive')
            ->withTrashed();

        Route::post('restore/{form_entry}', [FormEntryController::class, 'restore'])
            ->name('restore')
            ->withTrashed();

        Route::post('force-delete/{form_entry}', [FormEntryController::class, 'forceDelete'])
            ->name('force-delete')
            ->withTrashed();
    });

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
