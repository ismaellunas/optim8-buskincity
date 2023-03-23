<?php

namespace Tests\Browser\Pages\Backend;

use App\Models\Post;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class PostEdit extends Page
{
    private $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.posts.edit', ['post' => $this->post->id], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains('Edit Post');
        $browser->assertSee("Edit Post");
        $browser->assertSee("Title");
        $browser->assertSee("Content");
        $browser->assertValue('@inputTitle', $this->post->title);
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
            '@contentTabTrigger' => '#content-tab-trigger',
            '@seoTabTrigger' => '#seo-tab-trigger',
            '@inputTitle' => 'input[name=title]',
        ];
    }
}
