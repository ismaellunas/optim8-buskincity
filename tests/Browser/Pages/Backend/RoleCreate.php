<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class RoleCreate extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.roles.create', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains('Add New Role');
        $browser->assertSee('Add New Role');
        $browser->assertSee('Name');
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
        ];
    }
}
