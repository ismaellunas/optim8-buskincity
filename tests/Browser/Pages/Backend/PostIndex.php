<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class PostIndex extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.posts.index', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains(__('Posts'));
        $browser->assertSee(__('Published'));
        $browser->assertSee(__('Scheduled'));
        $browser->assertSee(__('Draft'));
        $browser->assertSeeLink(__('Create new'));
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@publishedTabTrigger' => '#published-tab-trigger',
            '@scheduledTabTrigger' => '#scheduled-tab-trigger',
            '@draftTabTrigger' => '#draft-tab-trigger',
            '@galleryViewButton' => 'button.gallery-view-button',
            '@listViewButton' => 'button.list-view-button',
        ];
    }
}
