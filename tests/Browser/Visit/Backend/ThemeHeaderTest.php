<?php

namespace Tests\Browser\Visit\Backend;

use Database\Seeders\MenuSeeder;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\ThemeHeader;
use Tests\Browser\Visit\BaseVisitTestCase;

class ThemeHeaderTest extends BaseVisitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(MenuSeeder::class);
    }

    /** @test */
    public function edit(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new ThemeHeader())
                ->clickNavigationTab()
                ->assertSee('Menu Items');
        });
    }
}
