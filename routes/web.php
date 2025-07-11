<?php

use App\Facades\Localization;
use App\Http\Controllers\{
    ApiPageBuilderComponentUserListController,
    ChangeLanguageController,
    CustomOAuthController,
    FormController,
    Frontend\DashboardController,
    Frontend\DonationController,
    Frontend\PageController,
    Frontend\PaymentController,
    Frontend\PostCategoryController,
    Frontend\PostController,
    Frontend\ProfileController as FrontendProfileController,
    Frontend\QrCodeController,
    Frontend\StripeController,
    Frontend\StylePageBuilderController,
    NewPasswordController,
    PasswordResetLinkController,
    RegisteredUserController,
    SitemapController,
    StoredCssController,
    TwoFactorAuthenticatedSessionController,
    UserPasswordController,
    UserProfileController,
    WebhookStripeController,
};
use App\Http\Middleware\HandleInertiaRequests;
use App\Services\SitemapService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

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

    Route::prefix('/payments')
        ->name('payments.')
        ->middleware('can:manageStripeConnectedAccount,App\Models\User')
        ->group(function() {
            Route::get('/', [PaymentController::class, 'index'])
                ->name('index');
        });

    Route::prefix('/payments/stripe')
        ->name('payments.stripe.')
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
    $loginLimiter = config('fortify.limiters.login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            'guest:'.config('fortify.guard'),
            $loginLimiter ? 'throttle:'.$loginLimiter : null,
            'recaptcha',
        ]));

    if (Features::enabled(Features::resetPasswords())) {
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware([
                'guest:'.config('fortify.guard'),
                'recaptchaForgotPasswordPage',
                'throttle:defaultRequest'
            ])
            ->name('password.email');
        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->middleware(['guest:'.config('fortify.guard')])
            ->name('password.update');
    }

    if (Features::enabled(Features::registration())) {
        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->middleware([
                'guest:'.config('fortify.guard'),
                'recaptchaRegisterPage',
            ]);
    }

    if (Features::enabled(Features::twoFactorAuthentication())) {
        $twoFactorLimiter = config('fortify.limiters.two-factor');

        Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
            ->middleware(array_filter([
                'guest:'.config('fortify.guard'),
                $twoFactorLimiter ? 'throttle:'.$twoFactorLimiter : null,
                'recaptcha',
            ]));
    }
});

Route::name('api.')
    ->prefix('api')
    ->withoutMiddleware(HandleInertiaRequests::class)
    ->middleware('throttle:api')
    ->group(function () {
        Route::get('/page-builder/components/user-list', ApiPageBuilderComponentUserListController::class)
            ->name('page-builder.components.user-list');
    }
);

Route::name('forms.')->prefix('forms')->group(function () {
    Route::get('schemas', [FormController::class, 'getSchemas'])
        ->name('schemas');
    Route::post('save', [FormController::class, 'submit'])
        ->name('save');
});

Route::post('webhooks/stripe', WebhookStripeController::class);

Route::get('css/pb-{uid_page_builder}.css', StylePageBuilderController::class)
    ->name('page.css')
    ->withoutMiddleware('web');

Route::get('css/stored/{css_name}', StoredCssController::class)
    ->name('css.stored')
    ->whereIn('css_name', array_values(config('constants.settings.generate_css')))
    ->withoutMiddleware('web');

if (App::environment('production')) {
    Route::group(
        [
            'domain' => config('telescope.domain', null),
            'prefix' => config('telescope.path'),
        ],
        function () {
            Route::redirect('/{view?}', '/admin/system-log');
        }
    );
}

Route::prefix(Localization::setLocale())
    ->middleware(['localizationRedirect'])
    ->withoutMiddleware(HandleInertiaRequests::class)
    ->group(function () {
        Route::get('language/{new_locale}', ChangeLanguageController::class)
            ->where('new_locale', '[a-zA-Z]{2}')
            ->name('language.change')
            ->middleware('changeLanguage');

        Route::get('sitemap_index.xml', [SitemapController::class, 'sitemaps'])
            ->name('sitemap');

        Route::get('{sitemapName}-sitemap.xml', [SitemapController::class, 'urls'])
            ->where('sitemapName', implode('|', SitemapService::sitemapNames()))
            ->name('sitemap.urls');

        Route::get('/', [PageController::class, 'homePage'])
            ->name('homepage')
            ->middleware('redirectOriginLanguage');

        Route::middleware('adjustOriginLanguage')->group(function () {
            Route::get(trans('routes.blog.index'), [PostController::class, 'index'])
                ->name('blog.index');

            Route::get(trans('routes.blog.category.index'), [PostCategoryController::class, 'index'])
                ->name('blog.category.index');

            Route::get(trans('routes.blog.show'), [PostController::class, 'show'])
                ->where('slug', '[\w\d\-\_]+')
                ->name('blog.show');

            Route::get('test-'.trans('routes.blog.show'), [PostController::class, 'showTestVue'])
                ->where('slug', '[\w\d\-\_]+')
                ->name('blog.show.test');

            Route::get('/{page_translation}', [PageController::class, 'show'])
                ->name('frontend.pages.show');
        });

        Route::get(trans('routes.frontend.profile'), [FrontendProfileController::class, 'show'])
            ->name('frontend.profile')
            ->middleware('publicPage:profile')
            ->scopeBindings();

        Route::get('/print/qrcode/{user:unique_key}', [QrCodeController::class, 'print'])
            ->name('frontend.print.qrcode')
            ->middleware('publicPage:profile');

        Route::get('donations/{user}/success', [DonationController::class, 'success'])
            ->name('donations.success');

        Route::post('donations/checkout/{user}', [DonationController::class, 'checkout'])
            ->name('donations.checkout')->middleware('throttle:checkout');
});
