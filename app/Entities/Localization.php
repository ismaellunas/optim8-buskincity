<?php

namespace App\Entities;

use App\Services\TranslationService;
use Mcamara\LaravelLocalization\LaravelLocalization;

class Localization extends LaravelLocalization
{
    public function __construct()
    {
        parent::__construct();
        $this->defaultLocale = TranslationService::getDefaultLocale();
    }
}
