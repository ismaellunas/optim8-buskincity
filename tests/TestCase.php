<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->app->environment('testing')) {
            config(['lunar.database.connection' => config('database.default')]);
        }
    }
}
