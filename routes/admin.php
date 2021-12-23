<?php

use App\Http\Controllers\{
    CategoryController,
    LanguageController,
    MediaController,
    PageController,
    PermissionController,
    PostController,
    RoleController,
    ThemeAdvanceController,
    ThemeColorController,
    ThemeFontController,
    ThemeFontSizeController,
    ThemeFooterController,
    ThemeFooterMenuController,
    ThemeHeaderController,
    ThemeHeaderMenuController,
    TranslationManagerController,
    UserController,
    UserRoleController,
};
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;

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

    Route::get('/profile', [UserProfileController::class, 'show'])
        ->name('profile.show');

    Route::name('theme.')->prefix('theme')->middleware(['can:system.theme'])->group(function () {
        Route::get('/color', [ThemeColorController::class, 'edit'])->name('color.edit');
        Route::post('/color', [ThemeColorController::class, 'update'])->name('color.update');
        Route::get('/font-size', [ThemeFontSizeController::class, 'edit'])->name('font-size.edit');
        Route::post('/font-size', [ThemeFontSizeController::class, 'update'])->name('font-size.update');

        Route::prefix('header')->name('header.')->group(function () {
            Route::get('/', [ThemeHeaderController::class, 'edit'])->name('edit');
            Route::post('/layout', [ThemeHeaderController::class, 'update'])->name('layout.update');
            Route::post('/menu-item', [ThemeHeaderMenuController::class, 'update'])->name('update-menu-item');
        });

        Route::prefix('footer')->name('footer.')->group(function () {
            Route::get('/', [ThemeFooterController::class, 'edit'])->name('edit');
            Route::post('/', [ThemeFooterController::class, 'update'])->name('layout.update');
            Route::post('/menu-item', [ThemeFooterMenuController::class, 'update'])->name('update-menu-item');
        });

        Route::get('/advance', [ThemeAdvanceController::class, 'edit'])->name('advance.edit');
        Route::post('/advance', [ThemeAdvanceController::class, 'update'])->name('advance.update');
        Route::get('/fonts', [ThemeFontController::class, 'edit'])->name('fonts.edit');
        Route::post('/fonts', [ThemeFontController::class, 'update'])->name('fonts.update');
    });

    Route::name('settings.')->prefix('settings')->middleware('can:system.language')->group(function () {
        Route::get('/languages', [LanguageController::class, 'edit'])
            ->name('languages.edit');
        Route::post('/languages', [LanguageController::class, 'update'])
            ->name('languages.update');

        Route::get('/translation-manager', [TranslationManagerController::class, 'edit'])
            ->name('translation-manager.edit');
        Route::post('/translation-manager', [TranslationManagerController::class, 'update'])
            ->name('translation-manager.update');
        Route::get('/translation-export/{locale}', [TranslationManagerController::class, 'export'])
            ->name('translation-manager.export');
        Route::post('/translation-import', [TranslationManagerController::class, 'import'])
            ->name('translation-manager.import');
    });

    Route::name('users.')->prefix('users')->group(function () {
        Route::get('/reassignment-candidates/{user}', [UserController::class, 'getReassignmentCandidates'])
            ->name('reassignment-candidates');
    });;
});

Route::name('api.')->prefix('api')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/media', [MediaController::class, 'apiStore'])
        ->name('media.store');

    Route::post('/theme/header/menu-item', [ThemeHeaderMenuController::class, 'apiValidateMenuItem'])
        ->name('theme.header.menu-item.validate');

    Route::post('/theme/footer/social-media', [ThemeFooterController::class, 'apiValidateSocialMedia'])
        ->name('theme.footer.social-media.validate');
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