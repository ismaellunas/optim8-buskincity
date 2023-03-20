<?php

namespace Tests\Browser\Pages\Backend;

use App\Models\Page as ModelsPage;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class PageEdit extends Page
{
    private $page;

    public function __construct(ModelsPage $page)
    {
        $this->page = $page;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.pages.edit', ['page' => $this->page->id], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains('Edit Page');
        $browser->assertSee('Edit Page');
        $browser->assertSee('Details');
        $browser->assertSee('Builder');
        $browser->assertValue('@inputTitle', $this->page->title);
        $browser->assertButtonEnabled('Update');
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
            '@inputTitle' => 'input[name=title]',
        ];
    }
}
