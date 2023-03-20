<?php

namespace Tests\Browser\Visit\Backend;

use Database\Seeders\StripeSettingSeeder;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\SettingStripe;
use Tests\Browser\Visit\BaseVisitTestCase;

class SettingStripeTest extends BaseVisitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StripeSettingSeeder::class);
    }

    /** @test */
    public function edit(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new SettingStripe());
        });
    }
}
