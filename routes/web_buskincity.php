<?php

use App\Http\Controllers\{
    Frontend\PerformerApplicationController,
};
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::resource('/performer-application', PerformerApplicationController::class)
        ->only(['index', 'store']);
});