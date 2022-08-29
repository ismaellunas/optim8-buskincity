<?php

use Illuminate\Support\Facades\Route;
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
    });
