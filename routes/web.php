<?php

use App\Facades\Localization;
use App\Http\Controllers\{
    ChangeLanguageController,
    CustomOAuthController,
    FormController,
    Frontend\DashboardController,
    Frontend\DonationController,
    Frontend\PageController,
    Frontend\PostCategoryController,
    Frontend\PostController,
    Frontend\ProfileController as FrontendProfileController,
    Frontend\StripeController,
    NewPasswordController,
    PasswordResetLinkController,
    RegisteredUserController,
    SitemapController,
    TwoFactorAuthenticatedSessionController,
    UserPasswordController,
    UserProfileController,
    WebhookStripeController,
};
use App\Services\SitemapService;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

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

Route::middleware([
    'auth:sanctum',
    'verified',
    'ensureLoginFromLoginRoute'
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [UserProfileController::class, 'show'])
        ->name('user.profile.show');

    Route::get('/user/profile', function () {
        return redirect()->route('dashboard');
    })->name('profile.show');

    Route::put('/user/set-password', [UserPasswordController::class, 'store'])
        ->name('user-password.set');

    Route::prefix('/payment-management/stripe')
        ->name('payment-management.stripe.')
        ->middleware('can:manageStripeConnectedAccount,App\Models\User')
        ->group(function() {
            Route::get('/', [StripeController::class, 'show'])
                ->name('show');

            Route::post('create-connected-account', [StripeController::class, 'createThenRedirect'])
                ->name('create-connected-account');

            Route::post('update-setting', [StripeController::class, 'updateSetting'])
                ->name('update-setting');

            Route::get('redirect-to-stripe', [StripeController::class, 'redirectToStripeAccount'])
                ->name('redirect-to-stripe');

            Route::get('account-link', [StripeController::class, 'accountLink'])
                ->name('account-link');

            Route::get('reauth', [StripeController::class, 'refresh'])
                ->middleware('signed')
                ->name('refresh');

            Route::get('return', [StripeController::class, 'return'])
                ->middleware('signed')
                ->name('return');
        });
});

Route::get('/oauth/{provider}/callback', [CustomOAuthController::class, 'handleProviderCallback'])
    ->middleware(config('socialstream.middleware', ['web']))
    ->name('oauth.callback');

Route::get('language/{new_locale}', ChangeLanguageController::class)
    ->where('new_locale', '[a-zA-Z]{2}')
    ->name('language.change');

Route::get('/user/privacy', function() {
    echo "Privacy page";
});
Route::get('/user/service', function() {
    echo "Service page";
});
Route::get('/user/remove-facebook', function() {
    echo "Remove facebook account page";
});

Route::group(['middleware' => config('fortify.middleware', ['web'])], function () {
    if (Features::enabled(Features::resetPasswords())) {
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware(['guest:'.config('fortify.guard'), 'recaptcha'])
            ->name('password.email');
        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->middleware(['guest:'.config('fortify.guard')])
            ->name('password.update');
    }

    if (Features::enabled(Features::registration())) {
        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->middleware(['guest:'.config('fortify.guard')]);
    }

    if (Features::enabled(Features::twoFactorAuthentication())) {
        $twoFactorLimiter = config('fortify.limiters.two-factor');

        Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
            ->middleware(array_filter([
                'guest:'.config('fortify.guard'),
                $twoFactorLimiter ? 'throttle:'.$twoFactorLimiter : null,
            ]));
    }
});

Route::name('forms.')->prefix('forms')->group(function () {
    Route::get('schemas', [FormController::class, 'getSchemas'])
        ->name('schemas');
    Route::post('save', [FormController::class, 'submit'])
        ->name('save');
});

Route::post('webhooks/stripe', WebhookStripeController::class);

Route::group([
    'prefix' => Localization::setLocale(),
    'middleware' => [ 'localizationRedirect' ]
], function () {
    Route::get('/', [PageController::class, 'homePage'])
        ->name('homepage')
        ->middleware('redirectLanguage');

    Route::get('sitemap_index.xml', [SitemapController::class, 'sitemaps'])
        ->name('sitemap');

    Route::get('{sitemapName}-sitemap.xml', [SitemapController::class, 'urls'])
        ->where('sitemapName', implode('|', SitemapService::sitemapNames()))
        ->name('sitemap.urls');

    Route::get('/blog', [PostController::class, 'index'])
        ->name('blog.index');

    Route::get('/category/{category_translation}', [PostCategoryController::class, 'index'])
        ->name('blog.category.index');

    Route::get('blog/{slug}', [PostController::class, 'show'])
        ->where('slug', '[\w\d\-\_]+')
        ->name('blog.show');

    Route::get('/{page_translation}', [PageController::class, 'show'])
        ->name('frontend.pages.show')
        ->middleware('redirectLanguage');

    Route::get('/profile/{user:unique_key}/{firstname_lastname?}', [FrontendProfileController::class, 'show'])
        ->name('frontend.profile')
        ->middleware('publicPage:profile')
        ->scopeBindings();

    Route::get('donations/{user}/success', [DonationController::class, 'success'])
        ->name('donations.success');
    Route::post('donations/checkout/{user}', [DonationController::class, 'checkout'])
        ->name('donations.checkout')->middleware('throttle:checkout');
});
