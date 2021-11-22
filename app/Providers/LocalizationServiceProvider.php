<?php

namespace App\Providers;

use App\Entities\Localization;
use Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider;
use Illuminate\Support\ServiceProvider;

class LocalizationServiceProvider extends LaravelLocalizationServiceProvider
{
    protected function registerBindings()
    {
        $this->app->singleton(Localization::class, function () {
            return new Localization();
        });

        $this->app->alias(Localization::class, 'laravellocalization');
    }
}
