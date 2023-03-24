<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class BlogCategoryCreate extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.categories.create', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains('Add New Category');
        $browser->assertSee('Add New Category');
        $browser->assertSee("Name");
        $browser->assertSee("Slug");
        $browser->assertButtonEnabled("Create");
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
