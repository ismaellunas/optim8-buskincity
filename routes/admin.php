<?php

use App\Http\Controllers\{
    CategoryController,
    DashboardController,
    LanguageController,
    MediaController,
    PageController,
    PostController,
    RoleController,
    StripeController,
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
    UserProfileController,
};

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;

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
    Route::get('/media-lists', [MediaController::class, 'lists'])
        ->name('media.lists');

    Route::resource('/categories', CategoryController::class)
        ->except(['show']);

    Route::resource('/posts', PostController::class)
        ->except(['show']);

    Route::resource('/users', UserController::class)
        ->except(['show']);
    Route::put('/users/{user}/password', [UserController::class, 'updatePassword'])
        ->name('users.password');

    Route::resource('/roles', RoleController::class);

    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware(['can:system.dashboard'])
        ->name('dashboard');

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

        Route::get('/translation-manager/create', [TranslationManagerController::class, 'create'])
            ->name('translation-manager.create');
        Route::post('/translation-manager/create', [TranslationManagerController::class, 'store'])
            ->name('translation-manager.store');
        Route::get('/translation-manager', [TranslationManagerController::class, 'edit'])
            ->name('translation-manager.edit');
        Route::post('/translation-manager', [TranslationManagerController::class, 'update'])
            ->name('translation-manager.update');
        Route::delete('/translation-manager/{translation}', [TranslationManagerController::class, 'destroy'])
            ->name('translation-manager.destroy');

        Route::get('/translation-export/{locale}', [TranslationManagerController::class, 'export'])
            ->name('translation-manager.export');
        Route::post('/translation-import', [TranslationManagerController::class, 'import'])
            ->name('translation-manager.import');
    });

    Route::name('settings.')->prefix('settings')->middleware('can:system.payment')->group(function () {
        Route::get('/stripe', [StripeController::class, 'edit'])
            ->name('stripe.edit');
        Route::post('/stripe', [StripeController::class, 'update'])
            ->name('stripe.update');
    });

    Route::name('users.')->prefix('users')->group(function () {
        Route::get('/reassignment-candidates/{user}', [UserController::class, 'getReassignmentCandidates'])
            ->name('reassignment-candidates');
        Route::post('/suspend/{user}', [UserController::class, 'suspend'])
            ->name('suspend');
        Route::post('/unsuspend/{user}', [UserController::class, 'unsuspend'])
            ->name('unsuspend');
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
    $twoFactorLimiter = config('fortify.limiters.two-factor');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            $limiter ? 'throttle:'.$limiter : null,
        ]))
        ->name('login.attempt');

    if (Features::enabled(Features::resetPasswords())) {
        Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
            ->middleware(['guest:'.config('fortify.guard')])
            ->name('password.request');

        Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
            ->middleware(['guest:'.config('fortify.guard')])
            ->name('password.reset');

        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware(['guest:'.config('fortify.guard')])
            ->name('password.email');

        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->middleware(['guest:'.config('fortify.guard')])
            ->name('password.update');
    }

    Route::get('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
        ->name('two-factor.login');

    Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            'guest:'.config('fortify.guard'),
            $twoFactorLimiter ? 'throttle:'.$twoFactorLimiter : null,
        ]))
        ->name('two-factor.login.attempt');
});

Route::redirect('/', '/admin/login');

if (Features::enabled(Features::emailVerification())) {
    if (config('fortify.views', true)) {
        Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
            ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard')])
            ->name('verification.notice');
    }
}