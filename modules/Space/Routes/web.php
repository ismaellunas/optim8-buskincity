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

Route::name('admin.')->prefix('admin/')->middleware([
    'auth:sanctum',
    'verified',
    'can:system.dashboard',
    'ensureLoginFromAdminLoginRoute',
])->group(function () {
    Route::resource('spaces', SpaceController::class)
        ->except(['show']);
    Route::prefix('spaces')->name('spaces.')->group(function() {
        Route::post('/move-node/{current}/{parent?}', 'SpaceController@moveNode')->name('move-node');
    });
});
