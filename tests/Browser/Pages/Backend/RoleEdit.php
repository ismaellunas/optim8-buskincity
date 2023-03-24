<?php

namespace Tests\Browser\Pages\Backend;

use App\Models\Role;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class RoleEdit extends Page
{
    public function __construct(
        private Role $role
    ) {}

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.roles.edit', ['role' => $this->role], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains('Edit Role');
        $browser->assertSee('Edit Role');
        $browser->assertValue('@inputName', $this->role->name);
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
            '@inputName' => 'input[name=name]',
        ];
    }
}
