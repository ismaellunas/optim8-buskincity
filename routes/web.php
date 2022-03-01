<?php

use App\Facades\Localization;
use App\Http\Controllers\{
    ChangeLanguageController,
    CustomOAuthController,
    DashboardController,
    FormController,
    Frontend\PageController,
    Frontend\PostCategoryController,
    Frontend\PostController,
    Frontend\ProfileController as FrontendProfileController,
    NewPasswordController,
    PasswordResetLinkController,
    StripeController,
};
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;

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

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [UserProfileController::class, 'show'])
        ->name('user.profile.show');

    Route::get('/user/profile', function () {
        return redirect()->route('dashboard');
    })->name('profile.show');

    Route::prefix('/payment-management/stripe')
        ->name('payment-management.stripe.')
        ->middleware(['can:payment.management'])
        ->group(function() {
            Route::get('/', [StripeController::class, 'show'])
                ->name('show');

            Route::post('create-connected-account', [StripeController::class, 'createThenRedirect'])
                ->name('create-connected-account');

            Route::get('redirect-to-stripe', [StripeController::class, 'redirectToStripeAccount'])
                ->name('redirect-to-stripe');
        });

    Route::name('payments.stripe.')->prefix('payments/stripe')->middleware(['can:payment.management'])->group(function() {
        Route::get(
            'payments/stripe/reauth/{user}',
            [StripeController::class, 'refresh']
        )->middleware('signed')->name('refresh');

        Route::get('return/{user}', function() {
            return redirect()->route('payment-management.stripe.show');
        })->middleware('signed')->name('return');
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

Route::group([
    'prefix' => Localization::setLocale(),
    'middleware' => [ 'localizationRedirect' ]
], function () {
    Route::get('/', [PageController::class, 'homePage'])->name('homepage');

    Route::get('/blog', [PostController::class, 'index'])
        ->name('blog.index');

    Route::get('/category/{id}', [PostCategoryController::class, 'index'])
        ->name('blog.category.index');

    Route::get('blog/{slug}', [PostController::class, 'show'])
        ->where('slug', '[\w\d\-\_]+')
        ->name('blog.show');

    Route::get('/{page_translation}', [PageController::class, 'show'])
        ->name('frontend.pages.show');

    // Route for Test translation
    Route::get('/test/translation', function () {
        return view('test.translation', [
            'title' => 'Test Translation'
        ]);
    })->name('test.translation');
});

Route::middleware(['guest:'.config('fortify.guard')])->group(function () {
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email')->middleware('recaptcha');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});

Route::name('forms.')->prefix('forms')->group(function () {
    Route::get('schemas', [FormController::class, 'getSchemas'])
        ->name('schemas');
    Route::post('save', [FormController::class, 'submit'])
        ->name('save');
});

Route::get('frontend/profiles/{user}', [FrontendProfileController::class, 'show']);
