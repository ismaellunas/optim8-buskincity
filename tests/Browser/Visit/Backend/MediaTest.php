<?php

namespace Tests\Browser\Visit\Backend;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\MediaIndex;
use Tests\Browser\Visit\BaseVisitTestCase;

class MediaTest extends BaseVisitTestCase
{
    /** @test */
    public function index(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new MediaIndex())
                ->click('@galleryViewButton')
                ->waitForInertiaSuccess()
                ->assertSee('Showing')

                ->click('@listViewButton')
                ->waitForInertiaSuccess()
                ->assertSee('Showing');
        });
    }
}
