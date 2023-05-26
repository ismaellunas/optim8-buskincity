<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class UserCreate extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.users.create', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains(__('Add New User'));
        $browser->assertSee(__('First name'));
        $browser->assertButtonEnabled(__('Create'));
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
