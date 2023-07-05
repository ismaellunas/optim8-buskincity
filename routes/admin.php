<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Controllers\{
    ApiOptionController,
    ApiPageBuilderController,
    ApiSettingController,
    ApiWidgetController,
    CategoryController,
    CookieConsentController,
    DashboardController,
    ErrorLogController,
    LanguageController,
    MediaController,
    PageController,
    PasswordResetLinkController,
    PostController,
    RoleController,
    SendUserPasswordResetEmailController,
    SettingKeyController,
    StripeController,
    SystemLogController,
    ThemeAdvanceController,
    ThemeColorController,
    ThemeFontController,
    ThemeFooterController,
    ThemeFooterMenuController,
    ThemeHeaderController,
    ThemeHeaderMenuController,
    TranslationManagerController,
    TwoFactorAuthenticatedSessionController,
    UserController,
    UserProfileController,
    VerifyEmailController,
};
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(array_filter([
    'auth:sanctum',
    'verified',
    'can:system.dashboard',
    env('MID_ENSURE_HOME_ENABLED', true) ? 'ensureLoginFromAdminLoginRoute' : null,
]))->group(function () {

    Route::resource('/pages', PageController::class)
        ->except(['show']);
    Route::post('/pages/duplicate/{page}', [PageController::class, 'duplicatePage'])
        ->name('pages.duplicate')
        ->can('page.add');
    Route::delete('/pages/translations/{page_translation:id}/destroy', [PageController::class, 'translationDestroy'])
        ->name('pages.translations.destroy')
        ->can('page.delete');

    Route::resource('/media', MediaController::class)
        ->except(['edit', 'show', 'update']);

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

    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->withTrashed()
        ->name('users.edit');
    Route::resource('/users', UserController::class)
        ->except(['show', 'edit']);
    Route::get('/users/trashed-records', [UserController::class, 'getTrashedRecords'])
        ->middleware('can:manageUserTrashed,App\Models\User')
        ->name('users.trashed-records');
    Route::put('/users/{user}/password', [UserController::class, 'updatePassword'])
        ->name('users.password');

    Route::get('users/password-reset/form-data', [SendUserPasswordResetEmailController::class, 'passwordResetFormData'])
        ->middleware('can:managePasswordResetEmail,App\Models\User')
        ->name('users.password-reset.form-data');

    Route::post('users/password-reset/send', SendUserPasswordResetEmailController::class)
        ->name('users.password-reset.send');

    Route::resource('/roles', RoleController::class);

    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [UserProfileController::class, 'show'])
        ->name('profile.show');

    Route::name('theme.')->prefix('theme')->middleware(['can:system.theme'])->group(function () {
        Route::get('/color', [ThemeColorController::class, 'edit'])->name('color.edit');
        Route::post('/color', [ThemeColorController::class, 'update'])->name('color.update');

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

    Route::name('settings.')->prefix('settings')->group(function () {
        Route::middleware('can:system.language')->group(function () {
            Route::get('/languages', [LanguageController::class, 'edit'])
                ->name('languages.edit');
            Route::post('/languages', [LanguageController::class, 'update'])
                ->name('languages.update');
        });

        Route::middleware('can:system.translation')->group(function () {
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

        Route::middleware('can:manageStripeSetting,App\Models\User')->group(function () {
            Route::get('/stripe', [StripeController::class, 'edit'])
                ->name('stripe.edit');
            Route::post('/stripe', [StripeController::class, 'update'])
                ->name('stripe.update');
        });

        Route::middleware('can:manageKeys,App\Models\Setting')->group(function () {
            Route::get('/keys', [SettingKeyController::class, 'edit'])
                ->name('keys.edit');
            Route::post('/keys', [SettingKeyController::class, 'update'])
                ->name('keys.update');
        });

        Route::middleware('can:system.cookie_consent')->group(function () {
            Route::get('/cookie-consent', [CookieConsentController::class, 'edit'])
                ->name('cookie-consent.edit');
            Route::post('/cookie-consent', [CookieConsentController::class, 'update'])
                ->name('cookie-consent.update');
        });
    });

    Route::name('users.')->prefix('users')->group(function () {
        Route::get('/reassignment-candidates/{user}', [UserController::class, 'getReassignmentCandidates'])
            ->name('reassignment-candidates');
        Route::post('/suspend/{user}', [UserController::class, 'suspend'])
            ->name('suspend');
        Route::post('/unsuspend/{user}', [UserController::class, 'unsuspend'])
            ->name('unsuspend');
    });

    Route::name('system-log.')->prefix('system-log')->middleware(['can:system.log'])->group(function () {
        Route::get('/', [SystemLogController::class, 'index'])
            ->name('index');
        Route::get('/search-users', [SystemLogController::class, 'searchUsers'])
            ->name('search-users');
    });

    Route::name('error-log.')
        ->prefix('error-log')
        ->group(function () {
            Route::get('/', [ErrorLogController::class, 'index'])
                ->name('index')
                ->middleware('can:viewAny,App\Models\ErrorLog');
            Route::delete('/all', [ErrorLogController::class, 'destroyAll'])
                ->name('destroy.all')
                ->middleware('can:multipleDelete,App\Models\ErrorLog');
            Route::delete('/{errorLog}', [ErrorLogController::class, 'destroy'])
                ->name('destroy')
                ->can('delete', 'errorLog');
            Route::post('/delete-checked', [ErrorLogController::class, 'destroyChecked'])
                ->name('destroy.checked')
                ->middleware('can:multipleDelete,App\Models\ErrorLog');
        });
});

Route::name('api.')
    ->prefix('api')
    ->middleware(['auth:sanctum', 'verified', 'throttle:api'])
    ->withoutMiddleware(HandleInertiaRequests::class)
    ->group(function () {
        Route::post('/media', [MediaController::class, 'apiStore'])
            ->name('media.store');

        Route::post('/theme/header/menu-item', [ThemeHeaderMenuController::class, 'apiValidateMenuItem'])
            ->name('theme.header.menu-item.validate');

        Route::post('/theme/footer/social-media', [ThemeFooterController::class, 'apiValidateSocialMedia'])
            ->name('theme.footer.social-media.validate');

        Route::get('/pages/{page}/is-used-by-menu/{locale?}', [PageController::class, 'isUsedByMenus'])
            ->middleware('can:page.edit')
            ->name('pages.is-used-by-menu');

        Route::get('/page-builder/country-options', [ApiPageBuilderController::class, 'countryOptions'])
            ->name('page-builder.country-options');

        Route::get('/page-builder/type-options', [ApiPageBuilderController::class, 'typeOptions'])
            ->name('page-builder.type-options');

        Route::get('/page-builder/user-list/role-options', [ApiPageBuilderController::class, 'userListRoleOptions'])
            ->name('page-builder.user-list.role-options');

        Route::get('/options/phone-countries', [ApiOptionController::class, 'phoneCountryOptions'])
            ->name('options.phone-countries');

        Route::get('/page-builder/post/category-options', [ApiPageBuilderController::class, 'postCategoryOptions'])
            ->name('page-builder.post.category-options');

        Route::get('/tinymce/key', [SettingKeyController::class, 'getTinyMCEKey'])
            ->name('tinymce.key');

        Route::get('widget/latest-registrations', [ApiWidgetController::class, 'getLatestRegistrations'])
            ->name('widget.latest-registrations');

        Route::get('widget/data/{uuid}', [ApiWidgetController::class, 'getStoredWidgetData'])
            ->name('widget.data');

        Route::get('/setting/max-file-size', [ApiSettingController::class, 'maxFileSize'])
            ->name('setting.max-file-size');
    });

Route::middleware(['guest:'.config('fortify.guard')])->group(function () {
    $limiter = config('fortify.limiters.login');
    $twoFactorLimiter = config('fortify.limiters.two-factor');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            $limiter ? 'throttle:'.$limiter : null,
            env('MID_RECAPTCHA_ENABLED', true) ? 'recaptchaAdminLoginPage' : null,
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
            ->name('password.email')->middleware([
                'recaptcha',
                'throttle:defaultRequest',
            ]);

        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->middleware(['guest:'.config('fortify.guard')])
            ->name('password.update');
    }

    if (Features::enabled(Features::twoFactorAuthentication())) {
        Route::get('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
            ->name('two-factor.login');

        Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
            ->middleware(array_filter([
                'guest:'.config('fortify.guard'),
                $twoFactorLimiter ? 'throttle:'.$twoFactorLimiter : null,
                'recaptcha',
            ]))
            ->name('two-factor.login.attempt');
    }
});

if (Features::enabled(Features::emailVerification())) {
    if (config('fortify.views', true)) {
        Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
            ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard')])
            ->name('verification.notice');
    }

    $verificationLimiter = config('fortify.limiters.verification', '6,1');

    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware([config('fortify.auth_middleware', 'auth').':'.config('fortify.guard'), 'signed', 'throttle:'.$verificationLimiter])
        ->name('verification.verify');
}

Route::redirect('/', '/admin/login');