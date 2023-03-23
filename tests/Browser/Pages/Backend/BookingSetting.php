<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class BookingSetting extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.booking.settings.edit', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains('Booking Settings');
        $browser->assertSee('Email');
        $browser->assertSee('Check In');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@checkInTabTrigger' => '#check-in-tab-trigger',
            '@emailTabTrigger' => '#email-tab-trigger',
            '@checkInForm' => '#check-in-form',
            '@emailForm' => '#email-form',
        ];
    }
}
