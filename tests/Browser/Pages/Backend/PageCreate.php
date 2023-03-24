<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class PageCreate extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.pages.create', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertSee('Add New Page');
        $browser->assertSee('Details');
        $browser->assertSee('Builder');
        $browser->assertButtonEnabled('Create');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@detailsTab' => '#details-tab',
            '@builderTab' => '#builder-tab',
            '@settingsTab' => '#settings-tab',
        ];
    }
}
