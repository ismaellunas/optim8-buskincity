<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class PostCreate extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.posts.create', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains('Add New Post');
        $browser->assertSee("Add New Post");
        $browser->assertSee("Title");
        $browser->assertSee("Content");
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
            '@contentTabTrigger' => '#content-tab-trigger',
            '@seoTabTrigger' => '#seo-tab-trigger',
        ];
    }
}
