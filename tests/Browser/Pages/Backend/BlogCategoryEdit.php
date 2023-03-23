<?php

namespace Tests\Browser\Pages\Backend;

use App\Models\Category;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class BlogCategoryEdit extends Page
{
    private $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.categories.edit', ['category' => $this->category], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains('Edit Category');
        $browser->assertSee("Edit Category");
        $browser->assertSee("Name");
        $browser->assertSee("Slug");
        $browser->assertButtonEnabled("Update");
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
