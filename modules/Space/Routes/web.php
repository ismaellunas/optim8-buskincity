<?php

use App\Facades\Localization;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Space\Http\Controllers\ContactController;
use Modules\Space\Http\Controllers\Frontend\SpaceController as FrontendSpaceController;
use Modules\Space\Http\Controllers\Frontend\SpaceEventController;
use Modules\Space\Http\Middleware\CanManageEvent;

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
    'verifyModule:Space',
])->group(function () {
    Route::resource('spaces', SpaceController::class)
        ->except(['show']);

    Route::prefix('spaces')->name('spaces.')->group(function() {
        Route::post('/update-manager/{space}', 'SpaceController@updateManagers')->name('update-managers');
        Route::get('/search-managers', 'SpaceController@searchManagers')->name('search-managers');

        Route::prefix('settings')->name('settings.')->group(function() {
            Route::get('/', 'SettingController@index')
                ->name('index')
                ->middleware('role:'.config('permission.admin_or_super_admin'));

            Route::get('space-types/records', 'SpaceTypeController@records')->name('space-types.records');
            Route::resource('space-types', SpaceTypeController::class)
                ->only(['store', 'update', 'destroy']);
        });
    });

    Route::resource('spaces.pages', PageController::class)
        ->only(['store', 'update']);

    Route::middleware(CanManageEvent::class)->group(function () {
        Route::resource('spaces.events', EventController::class)
            ->only(['store', 'update', 'show', 'destroy']);

        Route::get('spaces/{space}/event-records', 'EventController@records')
            ->name('spaces.events.records');
    });

    Route::name('api.')
        ->prefix('api')
        ->withoutMiddleware(HandleInertiaRequests::class)
        ->middleware('throttle:api')
        ->group(function () {
            Route::post('/spaces/contact', [ContactController::class, 'apiValidateContact'])
                ->name('spaces.contact.validate');

            Route::post('/spaces/settings/space-types', 'SpaceTypeController@apiValidateSpaceType')
                ->name('spaces.settings.space-types.validate');

            Route::get('/spaces/{space}/is-used-by-menu/{locale?}', 'SpaceController@isUsedByMenus')
                ->can('update', 'space')
                ->name('spaces.is-used-by-menu');
        });
});

Route::prefix(Localization::setLocale())
    ->middleware(['redirectIfModuleIsDisabled:Space', 'localizationRedirect', 'adjustOriginLanguage'])
    ->withoutMiddleware(HandleInertiaRequests::class)
    ->group(function () {
        Route::get(LaravelLocalization::transRoute('frontend.spaces.index'), [FrontendSpaceController::class, 'index'])
            ->name('frontend.spaces.index');
        Route::get(LaravelLocalization::transRoute('frontend.spaces.show'), [FrontendSpaceController::class, 'show'])
            ->name('frontend.spaces.show')
            ->where('slugs', '.+');
});

Route::prefix('api/space')
    ->name('api.space.')
    ->withoutMiddleware(HandleInertiaRequests::class)
    ->middleware(['throttle:api', 'verifyModule:Space'])
    ->group(function () {
        Route::get('space-events/{encryptedSpaceId}', [SpaceEventController::class, 'events'])
            ->name('space-events');
});
