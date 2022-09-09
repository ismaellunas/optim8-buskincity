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

//Route::prefix('ecommerce')->group(function() {
//    Route::get('/', 'EcommerceController@index');
//});

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
        Route::resource('/products', ProductController::class)
            ->except(['show']);

        Route::put('/product/{product}/event', [ProductEventController::class, 'update'])
            ->name('products.events.update');

        Route::resource('/orders', OrderController::class)
            ->only(['index', 'show']);

        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])
            ->name('orders.cancel');

        Route::get('/orders/{order}/reschedule', [OrderController::class, 'reschedule'])
            ->name('orders.reschedule');

        Route::post('/orders/{order}/reschedule', [OrderController::class, 'rescheduleUpdate'])
            ->name('orders.reschedule.update');

        Route::get('/orders/{order}/available-times/{date}', [OrderController::class, 'availableTimes'])
            ->name('orders.available-times');
    });

Route::
    prefix('ecommerce')
    ->name('ecommerce.')
    ->middleware([
        'auth:sanctum',
        'verified',
        'ensureLoginFromLoginRoute',
    ])
    ->group(function () {
        Route::resource('/products', Frontend\ProductController::class)
            ->only(['index', 'show']);

        Route::resource('/orders', Frontend\OrderController::class)
            ->only(['index', 'show']);
    });
