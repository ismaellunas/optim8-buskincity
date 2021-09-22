<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\{
    CategoryController,
    Frontend\PostController as FrontendPostController,
    PermissionController,
    PostController,
    RoleController,
    UserRoleController
};
use App\Services\TranslationService as TranslationSv;

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

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::resource('/roles', RoleController::class);
    Route::resource('/permissions', PermissionController::class);
    Route::resource('/user-roles', UserRoleController::class);
});

Route::name('admin.')->prefix('admin')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::resource('/pages', App\Http\Controllers\PageController::class)
        ->except(['show']);
    Route::resource('/media', App\Http\Controllers\MediaController::class);
    Route::resource('/categories', CategoryController::class);
    Route::post(
        '/media/update-image/{medium}',
        [App\Http\Controllers\MediaController::class, 'updateImage']
    )->name('media.update-image');
    Route::post(
        '/media/save-as-media/{medium}',
        [App\Http\Controllers\MediaController::class, 'saveAsMedia']
    )->name('media.save-as-media');

    Route::get('/media-list/image', [App\Http\Controllers\MediaController::class, 'listImages'])
        ->name('media.list.image');
    Route::resource('/posts', PostController::class);
});

Route::name('api.admin.')->prefix('api/admin')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/media', [App\Http\Controllers\MediaController::class, 'apiStore'])
        ->name('media.store');
});

/* ---------- FRONTEND ---------- */
Route::get('/', function () {
    return redirect(TranslationSv::currentLanguage());
});

Route::get('language/{new_locale}', App\Http\Controllers\ChangeLanguageController::class)
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

Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => '[a-zA-Z]{2}'],
    'middleware' => 'setLocale',
], function () {
    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

    Route::get('/blog', [FrontendPostController::class, 'index'])
        ->name('blog.index');

    Route::get('blog/{slug}', [FrontendPostController::class, 'show'])
        ->where('slug', '[\w\d\-\_]+')
        ->name('blog.show');

    Route::get('/{page_translation}', [App\Http\Controllers\PageController::class, 'show'])
        ->name('pages.show');
});
