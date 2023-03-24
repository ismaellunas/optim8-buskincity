<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class SettingKeys extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.settings.keys.edit', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains('Keys');
        $browser->assertSee('Keys');
        $browser->assertSee('Google Recaptcha Site');
        $browser->assertButtonEnabled('Save');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@element' => '#selector',
        ];
    }
}
