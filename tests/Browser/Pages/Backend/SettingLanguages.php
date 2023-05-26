<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class SettingLanguages extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.settings.languages.edit', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains(__('Languages'));
        $browser->assertButtonEnabled(__('Update'));
        $browser->assertSee(__('Default language'));
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [];
    }
}
