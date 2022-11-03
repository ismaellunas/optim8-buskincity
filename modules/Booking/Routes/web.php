<?php

use Illuminate\Support\Facades\Route;
use Modules\Booking\Http\Controllers\Frontend\OrderController as FrontendOrderController;
use Modules\Booking\Http\Controllers\Frontend\ProductController as FrontendProductController;
use Modules\Booking\Http\Controllers\OrderController;
use Modules\Booking\Http\Controllers\ProductEventController;

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

Route::prefix('admin/booking')->name('admin.booking.')->middleware([
    'auth:sanctum',
    'verified',
    'can:system.dashboard',
    'ensureLoginFromAdminLoginRoute',
])->group(function () {

    Route::resource('products', ProductController::class)
        ->except(['show']);

    Route::put('/products/{product}/event', [ProductEventController::class, 'update'])
        ->name('products.events.update')
        ->can('update', 'product');

    Route::get('/products/{product}/allowed-dates/{month}/{year}', [FrontendProductController::class, 'allowedDates'])
        ->name('products.allowed-dates')
        ->can('update', 'product');

    Route::resource('/orders', OrderController::class)
        ->only(['index', 'show']);

    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])
        ->name('orders.cancel')
        ->can('cancelBooking', 'order');

    Route::get('/orders/{order}/reschedule', [OrderController::class, 'reschedule'])
        ->name('orders.reschedule')
        ->can('rescheduleBooking', 'order');

    Route::post('/orders/{order}/reschedule', [OrderController::class, 'rescheduleUpdate'])
        ->name('orders.reschedule.update')
        ->can('rescheduleBooking', 'order');

    Route::get('/orders/{order}/available-times/{date}', [OrderController::class, 'availableTimes'])
        ->name('orders.available-times')
        ->can('rescheduleBooking', 'order');
});

Route::prefix('booking')->name('booking.')->middleware([
    'auth:sanctum',
    'verified',
    'ensureLoginFromLoginRoute',
])->group(function () {

    Route::prefix('products')->name('products.')->group(function () {

        Route::get('/', [FrontendProductController::class, 'index'])
            ->name('index')
            ->middleware('can:viewAny,Modules\Ecommerce\Entities\Product');

        Route::get('/{product}', [FrontendProductController::class, 'show'])
            ->name('show')
            ->can('showFrontendProductEvent', 'product');

        Route::get('/{product}/available-times/{date}', [FrontendProductController::class, 'availableTimes'])
            ->name('available-times')
            ->can('showFrontendProductEvent', 'product');

        Route::get('/{product}/allowed-dates/{month}/{year}', [FrontendProductController::class, 'allowedDates'])
            ->name('allowed-dates')
            ->can('showFrontendProductEvent', 'product');
    });

    Route::resource('/orders', Frontend\OrderController::class)
        ->only(['index', 'show']);

    Route::prefix('orders')->name('orders.')->group(function () {

        Route::get('/{order}/reschedule', [FrontendOrderController::class, 'reschedule'])
            ->name('reschedule')
            ->can('rescheduleBooking', 'order');

        Route::post('/{order}/reschedule', [FrontendOrderController::class, 'rescheduleUpdate'])
            ->name('reschedule.update');

        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])
            ->name('cancel')
            ->can('cancelBooking', 'order');

        Route::post('/{order}/check-in', Modules\Booking\Http\Controllers\Frontend\CheckInController::class)
            ->name('check-in');

        Route::post('/{product}/book-event', [FrontendOrderController::class, 'bookEvent'])
            ->name('book-event');
    });
});
