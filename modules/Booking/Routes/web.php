<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;
use Modules\Booking\Http\Controllers\ApiPageBuilderComponent\EventsCalendarController;
use Modules\Booking\Http\Controllers\ApiWidgetController;
use Modules\Booking\Http\Controllers\Frontend\OrderController as FrontendOrderController;
use Modules\Booking\Http\Controllers\Frontend\ProductController as FrontendProductController;
use Modules\Booking\Http\Controllers\Frontend\EventController as FrontendEventController;
use Modules\Booking\Http\Controllers\Frontend\UpcomingEventController;
use Modules\Booking\Http\Controllers\OrderController;
use Modules\Booking\Http\Controllers\ProductEventController;
use Modules\Booking\Http\Controllers\ProductEventCrudController;
use Modules\Booking\Http\Controllers\ProductEventScheduleController;
use Modules\Booking\Http\Controllers\ProductSpaceController;

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

Route::prefix('admin/booking')->name('admin.booking.')->middleware(array_filter([
    'auth:sanctum',
    'verified',
    'can:system.dashboard',
    env('MID_ENSURE_HOME_ENABLED', true) ? 'ensureLoginFromAdminLoginRoute' : null,
    'verifyModule:Booking',
]))->group(function () {

    Route::resource('products', ProductController::class)
        ->except(['show']);

    Route::put('/products/{product}/event', [ProductEventController::class, 'update'])
        ->name('products.events.update')
        ->can('update', 'product');

    Route::resource('products.product-events', ProductEventCrudController::class)
        ->only(['store', 'update', 'show', 'destroy'])
        ->middleware('can:update,product');

    Route::get('products/{product}/product-event-records', [ProductEventCrudController::class, 'records'])
        ->name('products.product-events.records')
        ->can('update', 'product');

    Route::get('products/{product}/product-events/{productEvent}/schedule', [ProductEventScheduleController::class, 'edit'])
        ->name('products.product-events.schedule.edit')
        ->can('update', 'product');

    Route::put('products/{product}/product-events/{productEvent}/schedule', [ProductEventScheduleController::class, 'update'])
        ->name('products.product-events.schedule.update')
        ->can('update', 'product');

    Route::get('/products/{product}/allowed-dates/{month}/{year}', [FrontendProductController::class, 'allowedDates'])
        ->name('products.allowed-dates')
        ->can('update', 'product');

    Route::put('/products/{product}/space', [ProductSpaceController::class, 'update'])
        ->name('products.spaces.update')
        ->can('update', 'product')
        ->middleware('verifyModule:Space',);

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

    Route::middleware('role:'.config('permission.admin_or_super_admin'))->group(function () {
        Route::get('/settings', 'SettingController@edit')
            ->name('settings.edit');
        Route::post('/settings/update', 'SettingController@update')
            ->name('settings.update');
    });
});

Route::prefix('booking')->name('booking.')->middleware(array_filter([
    'auth:sanctum',
    'verified',
    env('MID_ENSURE_HOME_ENABLED', true) ? 'ensureLoginFromLoginRoute' : null,
    'verifyModule:Booking',
]))->group(function () {

    Route::prefix('products')
        ->name('products.')
        ->middleware('can:showFrontendProduct,Modules\Ecommerce\Entities\Product')
        ->group(function () {

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

    Route::prefix('events')
        ->name('events.')
        ->group(function () {

        Route::get('/{event}', [FrontendEventController::class, 'show'])
            ->name('show')
            ->can('showFrontendProductEvent', 'event');

        Route::get('/{event}/available-times/{date}', [FrontendEventController::class, 'availableTimes'])
            ->name('available-times')
            ->can('showFrontendProductEvent', 'event');

        Route::get('/{event}/allowed-dates/{month}/{year}', [FrontendEventController::class, 'allowedDates'])
            ->name('allowed-dates')
            ->can('showFrontendProductEvent', 'event');
    });

    Route::middleware('can:showFrontendOrder,Modules\Ecommerce\Entities\Order')
        ->group(function () {
            Route::resource('/orders', Frontend\OrderController::class)
                ->only(['index', 'show'])
                ->middleware('can:showFrontendOrder,Modules\Ecommerce\Entities\Order',);

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

                Route::post('/{product}/book-event-batch', [FrontendOrderController::class, 'bookEventBatch'])
                    ->name('book-event-batch');
            });
        });
});

Route::prefix('api/booking')
    ->name('api.booking.')
    ->withoutMiddleware(HandleInertiaRequests::class)
    ->middleware('throttle:api')
    ->group(function () {

        Route::prefix('events-calendar')
            ->name('events-calendar.')
            ->middleware('verifyModule:Booking,Space,OR')
            ->group(function () {
                Route::get('/', [EventsCalendarController::class, 'index'])
                    ->name('index');

                Route::get('location-options', [EventsCalendarController::class, 'getLocationOptions'])
                    ->name('location-options');
        });

        Route::middleware('verifyModule:Booking')->group(function () {
            Route::get('widget/latest-bookings', [ApiWidgetController::class, 'getLatestBookings'])
                ->name('widget.latest-bookings');

            Route::get('upcoming-events/{userUniqueKey}', [UpcomingEventController::class, 'events'])
                ->name('upcoming-events');
        });
});
