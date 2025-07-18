<?php

namespace App\Providers;

use App\Services\SettingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    public const HOME_ADMIN = '/admin/dashboard';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->removeIndexPhpFromUrl();

        $this->configureRateLimiting();

        $this->routes(function () {
            try {

                $domainRedirections = app(SettingService::class)->getDomainRedirections();

            } catch (QueryException $e) {

                if ($e->getCode() == '42P01') {
                    $domainRedirections = [];
                } else {
                    throw $e;
                }

            } catch (\Predis\Connection\ConnectionException $e) {

                $domainRedirections = [];

            }

            foreach ($domainRedirections as $destination) {
                Route::domain($destination->from)->group(function () use ($destination) {
                    Route::permanentRedirect('/', $destination->to);

                    Route::any('/{any}', function (Request $request, $any) use ($destination) {
                        return redirect((
                            $destination->to."/".$any
                            .(!empty($request->query()) ? "?".$request->getQueryString() : "")
                        ), 301);
                    })->where('any', '.*');
                });
            }

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            // Route based on App Id
            $AppIdRoute = $this->fileRouteName('admin');
            if (file_exists($this->basePathRoute($AppIdRoute))) {

                Route::middleware('web')
                    ->namespace($this->namespace)
                    ->prefix('admin')
                    ->name('admin.')
                    ->group(base_path('routes/' . $AppIdRoute));
            }

            $AppIdRoute = $this->fileRouteName('web');
            if (file_exists($this->basePathRoute($AppIdRoute))) {

                Route::middleware('web')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/' . $AppIdRoute));
            }

            Route::middleware('web')
                ->namespace($this->namespace)
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });

        Route::model('medium', \App\Models\Media::class);
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('checkout', function (Request $request) {
            $max = config('constants.throttle.checkout');

            return Limit::perMinute($max)->by($request->session()->getId());
        });

        RateLimiter::for('defaultRequest', function (Request $request) {
            $max = config('constants.throttle.default');

            return Limit::perMinute($max)->by($request->session()->getId());
        });
    }

    private function fileRouteName(string $name): string
    {
        $appId = config('app.id');

        return $name .'_' . $appId . '.php';
    }

    private function basePathRoute(string $fileName): string
    {
        return base_path() . '/routes/' . $fileName;
    }

    private function removeIndexPhpFromUrl()
    {
        if (Str::contains(request()->getRequestUri(), '/index.php/')) {
            $url = Str::replace('index.php/', '', request()->getRequestUri());

            if (strlen($url) > 0) {
                header("Location: $url", true, 301);
                exit;
            }
        }
    }
}
