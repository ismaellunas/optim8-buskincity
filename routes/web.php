<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserRoleController;

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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/user/privacy', function() {
    echo "Privacy page";
});
Route::get('/user/service', function() {
    echo "Service page";
});
Route::get('/user/remove-facebook', function() {
    echo "Remove facebook account page";
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::resource('/roles', RoleController::class);
    Route::resource('/permissions', PermissionController::class);
    Route::resource('/user-roles', UserRoleController::class);
});

Route::name('admin.')->prefix('admin')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::resource('/pages', App\Http\Controllers\PageController::class);
    Route::resource('/media', App\Http\Controllers\MediaController::class);
    Route::post(
        '/media/upload-image',
        [App\Http\Controllers\PageController::class, 'uploadImage']
    )->name('media.upload-image');
});
