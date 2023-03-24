<?php

namespace Tests\Browser\Pages\Backend;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class UserEdit extends Page
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.users.edit', ['user' => $this->user], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains('Edit User');
        $browser->assertSee('First Name');
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
