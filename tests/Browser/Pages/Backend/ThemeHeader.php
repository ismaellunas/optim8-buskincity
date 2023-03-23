<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class ThemeHeader extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.theme.header.edit', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains('Header');
        $browser->assertSee('Header');
        $browser->assertSee('Logo');
        $browser->assertButtonEnabled("Save");
    }

    public function clickLayoutTab(Browser $browser)
    {
        $browser->click('@layoutTabTrigger');
    }

    public function clickNavigationTab(Browser $browser)
    {
        $browser->click('@navigationTabTrigger');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@layoutTabTrigger' => '#layout-tab-trigger',
            '@navigationTabTrigger' => '#navigation-tab-trigger',
        ];
    }
}
