<?php

namespace Tests\Browser\Visit\Backend;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\ThemeAdvanced;
use Tests\Browser\Visit\BaseVisitTestCase;

class ThemeAdvancedTest extends BaseVisitTestCase
{
    /** @test */
    public function edit(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new ThemeAdvanced());
        });
    }
}
