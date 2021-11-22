<?php

use App\Entities\Facades\Localization;
use App\Http\Controllers\{
    ChangeLanguageController,
    Frontend\PageController,
    Frontend\PostController,
    Frontend\PostCategoryController
};
use App\Services\TranslationService as TranslationSv;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
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
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [UserProfileController::class, 'show'])
        ->name('user.profile.show');

    Route::get('/user/profile', function () {
        return redirect()->route('dashboard');
    })->name('profile.show');
});

// Route::get('/', function () {
//     return redirect(TranslationSv::currentLanguage());
// });

Route::get('language/{new_locale}', ChangeLanguageController::class)
    ->where('new_locale', '[a-zA-Z]{2}')
    ->name('language.change');

Route::name('status-code.')->group(function () {
    Route::get('404', function () {
        return Inertia::render('PageNotFound');
    })->name('404');
});

Route::get('/user/privacy', function() {
    echo "Privacy page";
});
Route::get('/user/service', function() {
    echo "Service page";
});
Route::get('/user/remove-facebook', function() {
    echo "Remove facebook account page";
});

Route::get('test-theme', function () {
    return Inertia::render('TestTheme', [
        'webfontsUrl' => 'https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key='.config('constants.google_api_key'),
    ]);
});

Route::group([
    'prefix' => Localization::setLocale(),
    'middleware' => [ 'localizationRedirect' ]
], function () {
    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    })->name('homepage');

    Route::get('/blog', [PostController::class, 'index'])
        ->name('blog.index');

    Route::get('/category/{id}', [PostCategoryController::class, 'index'])
        ->name('blog.category.index');

    Route::get('blog/{slug}', [PostController::class, 'show'])
        ->where('slug', '[\w\d\-\_]+')
        ->name('blog.show');

    Route::get('/{page_translation}', [PageController::class, 'show'])
        ->name('frontend.pages.show');
});
