<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\AuthTestController;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    //
});

Route::get('/login-test', [AuthTestController::class, 'login'])
    ->name('login-test');
