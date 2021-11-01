<?php

use App\Http\Controllers\{
    CategoryController,
    HeaderController,
    MediaController,
    NavigationController,
    PageController,
    PermissionController,
    PostController,
    RoleController,
    ThemeColorController,
    ThemeFontSizeController,
    UserController,
    UserRoleController,
};
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use App\Entities\CloudinaryStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::resource('/pages', PageController::class)
        ->except(['show']);

    Route::resource('/media', MediaController::class)
        ->except(['edit', 'show']);

    Route::post('/media/update-image/{medium}', [MediaController::class, 'updateImage'])
        ->name('media.update-image');
    Route::post('/media/save-as-image/{medium}', [MediaController::class, 'saveAsImage'])
        ->name('media.save-as-image');
    Route::get('/media-list/image', [MediaController::class, 'listImages'])
        ->name('media.list.image');

    Route::resource('/categories', CategoryController::class)
        ->except(['show']);

    Route::resource('/posts', PostController::class)
        ->except(['show']);

    Route::resource('/users', UserController::class)
        ->except(['show']);
    Route::put('/users/{user}/password', [UserController::class, 'updatePassword'])
        ->name('users.password');

    Route::resource('/roles', RoleController::class);

    Route::resource('/permissions', PermissionController::class);

    Route::resource('/user-roles', UserRoleController::class);

    Route::get('dashboard', function () {
        return Inertia::render('AdminDashboard');
    })->middleware(['can:system.dashboard'])->name('dashboard');

    Route::name('theme.')->prefix('theme')->group(function () {
        Route::get('/color', [ThemeColorController::class, 'edit'])->name('color.edit');
        Route::post('/color', [ThemeColorController::class, 'update'])->name('color.update');
        Route::get('/font-size', [ThemeFontSizeController::class, 'edit'])->name('font-size.edit');
        Route::post('/font-size', [ThemeFontSizeController::class, 'update'])->name('font-size.update');

        Route::prefix('header')->name('header.')->group(function () {
            Route::get('/', [HeaderController::class, 'index'])->name('index');
            Route::post('/layout', [HeaderController::class, 'updateLayout'])->name('layout.update');
            Route::post('/logo', [HeaderController::class, 'updateLogo'])->name('logo.update');

            Route::resource('navigation', NavigationController::class);
            Route::put('navigation/update/format', [NavigationController::class, 'updateFormat'])->name('navigation.update.format');
        });
    });
});

Route::name('api.')->prefix('api')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/media', [MediaController::class, 'apiStore'])
        ->name('media.store');
});

Route::middleware(['guest:'.config('fortify.guard')])->group(function () {
    $limiter = config('fortify.limiters.login');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            $limiter ? 'throttle:'.$limiter : null,
        ]))
        ->name('login.attempt');
});

Route::redirect('/', '/admin/login');
