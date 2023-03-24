<?php

namespace Tests\Browser\Visit\Backend;

use App\Models\Language;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\SettingLanguages;
use Tests\Browser\Visit\BaseVisitTestCase;

class SettingLanguagesTest extends BaseVisitTestCase
{
    /** @test */
    public function edit(): void
    {
        $this->browse(function (Browser $browser) {
            $language = Language::inRandomOrder()->first();

            $browser
                ->loginAs($this->user)
                ->visit(new SettingLanguages())
                ->assertSee($language->name)
            ;
        });
    }
}
