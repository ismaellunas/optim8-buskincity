<?php

namespace App\Providers;

use App\Entities\Translator;
use Spatie\TranslationLoader\TranslationServiceProvider as SpatieTranslationServiceProvider;

class TranslationServiceProvider extends SpatieTranslationServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];

            $locale = $app->getLocale();

            $trans = new Translator($loader, $locale);

            $trans->setFallback($app->getFallbackLocale());

            return $trans;
        });
    }
}
