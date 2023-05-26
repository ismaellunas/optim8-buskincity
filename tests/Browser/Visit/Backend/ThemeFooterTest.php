<?php

namespace Tests\Browser\Visit\Backend;

use Database\Seeders\MenuSeeder;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\ThemeFooter;
use Tests\Browser\Visit\BaseVisitTestCase;

class ThemeFooterTest extends BaseVisitTestCase
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
                ->visit(new ThemeFooter())
                ->clickNavigationTab()
                ->assertSee(__('Menu items'))
                ->assertButtonEnabled('Add new segment');
        });
    }
}
