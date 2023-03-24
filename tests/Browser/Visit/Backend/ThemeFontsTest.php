<?php

namespace Tests\Browser\Visit\Backend;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\ThemeFonts;
use Tests\Browser\Visit\BaseVisitTestCase;

class ThemeFontsTest extends BaseVisitTestCase
{
    /** @test */
    public function edit(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new ThemeFonts());
        });
    }
}
