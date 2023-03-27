<?php

namespace Tests\Browser\Visit\Backend;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\SettingTranslationManager;
use Tests\Browser\Visit\BaseVisitTestCase;

class SettingTranslationManagerTest extends BaseVisitTestCase
{
    /** @test */
    public function edit(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new SettingTranslationManager())
                ->click('@importButton')
                ->waitFor('@importModal')
                ->assertSeeIn('@importModal', 'Import')
                ->assertSeeIn('@importModal', 'File')
                ->assertButtonEnabled('Submit')
                ->click('@closeModalButton')
                ->click('@groupFilterTrigger')
                ->assertSee('No Group')
            ;
        });
    }
}
