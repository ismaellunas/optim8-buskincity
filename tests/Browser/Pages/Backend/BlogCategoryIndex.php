<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class BlogCategoryIndex extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.categories.index', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains(__('Categories'));
        $browser->assertSee(__('Categories'));
        $browser->assertSeeLink(__("Create new"));
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
