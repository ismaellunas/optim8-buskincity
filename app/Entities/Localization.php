<?php

namespace App\Entities;

use Mcamara\LaravelLocalization\LaravelLocalization;

class Localization extends LaravelLocalization
{
    public function __construct()
    {
        parent::__construct();
        $this->defaultLocale = 'en';
    }
}
