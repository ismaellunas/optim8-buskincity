<?php

namespace Modules\FormBuilder\Providers;

use Illuminate\Support\ServiceProvider;
use Shortcode;
use Modules\FormBuilder\Shortcodes\FormBuilder;

class ShortcodesServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Shortcode::register('form-builder', FormBuilder::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
