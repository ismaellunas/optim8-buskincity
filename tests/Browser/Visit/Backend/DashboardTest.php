<?php

namespace Tests\Browser\Visit\Backend;

use Laravel\Dusk\Browser;
use Tests\Browser\Visit\BaseVisitTestCase;

class DashboardTest extends BaseVisitTestCase
{
    /** @test */
    public function dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit('/admin/dashboard')
                ->waitForText('Dashboard')
                ->assertSee('Dashboard')
                ->assertSee('Pages');
        });
    }
}
