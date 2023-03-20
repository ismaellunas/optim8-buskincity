<?php

namespace Tests\Browser\Visit\Backend;

use Laravel\Dusk\Browser;
use Tests\Browser\Visit\BaseVisitTestCase;

class LoginTest extends BaseVisitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->resize(1024, 768)
                ->visit('/admin/login')
                ->waitForText('Welcome Back')
                ->assertSee('Log In');
        });
    }
}
