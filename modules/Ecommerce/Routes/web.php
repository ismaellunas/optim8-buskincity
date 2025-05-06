<?php

use Illuminate\Support\Facades\Route;

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

Route::prefix('admin/ecommerce')->name('admin.ecommerce.')->middleware(array_filter([
    'auth:sanctum',
    'can:system.dashboard',
    env('MID_ENSURE_HOME_ENABLED', true) ? 'ensureLoginFromAdminLoginRoute' : null,
]))->group(function () {
    Route::prefix('products')->name('products.')->group(function() {
        Route::middleware('can:manageManager,Modules\Ecommerce\Entities\Product')->group(function () {
            Route::get('{product}/managers/search', 'ProductManagerController@search')
                ->name('managers.search');
            Route::post('{product}/managers/update', 'ProductManagerController@updateManagers')
                ->name('managers.update');
        });
    });
});
