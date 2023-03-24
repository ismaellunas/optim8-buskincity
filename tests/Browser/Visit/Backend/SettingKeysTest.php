<?php

namespace Tests\Browser\Visit\Backend;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\SettingKeys;
use Tests\Browser\Visit\BaseVisitTestCase;

class SettingKeysTest extends BaseVisitTestCase
{
    /** @test */
    public function edit(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new SettingKeys())
                ->screenshot('setting_keys')
            ;
        });
    }
}
