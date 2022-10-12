<?php

use Illuminate\Support\Facades\Route;
use Modules\Ecommerce\Http\Controllers\OrderController;
use Modules\Ecommerce\Http\Controllers\ProductEventController;

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
Route::
    prefix('admin/ecommerce')
    ->name('admin.ecommerce.')
    ->middleware([
        'auth:sanctum',
        'verified',
        'can:system.dashboard',
        'ensureLoginFromAdminLoginRoute',
    ])
    ->group(function () {

        Route::prefix('products')->name('products.')->group(function() {
            Route::middleware('can:manageManager,Modules\Ecommerce\Entities\Product')->group(function () {
                Route::get('{product}/managers/search', 'ProductManagerController@search')
                    ->name('managers.search');
                Route::post('{product}/managers/update', 'ProductManagerController@updateManagers')
                    ->name('managers.update');
            });
        });

        Route::resource('/products', ProductController::class)
            ->except(['show']);

        Route::put('/product/{product}/event', [ProductEventController::class, 'update'])
            ->name('products.events.update')
            ->can('update', 'product');

        //Route::get('/products/{product}/allowed-dates/{month}/{year}', [FrontendProductController::class, 'allowedDates'])
        //    ->name('products.allowed-dates')
        //    ->can('update', 'product');

        Route::resource('/orders', OrderController::class)
            ->only(['index', 'show']);

        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])
            ->name('orders.cancel')
            ->can('cancel', 'order');

        Route::get('/orders/{order}/reschedule', [OrderController::class, 'reschedule'])
            ->name('orders.reschedule')
            ->can('reschedule', 'order');

        Route::post('/orders/{order}/reschedule', [OrderController::class, 'rescheduleUpdate'])
            ->name('orders.reschedule.update')
            ->can('reschedule', 'order');

        Route::get('/orders/{order}/available-times/{date}', [OrderController::class, 'availableTimes'])
            ->name('orders.available-times')
            ->can('reschedule', 'order');
    });
