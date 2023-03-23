<?php

namespace Tests\Browser\Visit\Backend;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\ThemeFontSizes;
use Tests\Browser\Visit\BaseVisitTestCase;

class ThemeFontSizesTest extends BaseVisitTestCase
{
    /** @test */
    public function edit(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new ThemeFontSizes());
        });
    }
}
