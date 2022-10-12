<?php

use Illuminate\Support\Facades\Route;
use Modules\Booking\Http\Controllers\Frontend\OrderController as FrontendOrderController;
use Modules\Booking\Http\Controllers\Frontend\ProductController as FrontendProductController;
use Modules\Booking\Http\Controllers\OrderController;

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

//Route::prefix('booking')->group(function() {
//    Route::get('/', 'BookingController@index');
//});
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
            ->can('reschedule', 'order');

        Route::post('/{order}/reschedule', [FrontendOrderController::class, 'rescheduleUpdate'])
            ->name('reschedule.update');

        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])
            ->name('cancel')
            ->can('cancel', 'order');

        Route::post('/{product}/book-event', [FrontendOrderController::class, 'bookEvent'])
            ->name('book-event');
    });
});
