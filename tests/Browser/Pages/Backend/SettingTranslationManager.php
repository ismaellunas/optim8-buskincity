<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class SettingTranslationManager extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('admin.settings.translation-manager.edit', [], false);
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitForInertiaNavigate();
        $browser->assertPathIs($this->url());
        $browser->assertTitleContains('Translation Manager');
        $browser->assertButtonEnabled('Export');
        $browser->assertButtonEnabled('Import');
        $browser->assertButtonEnabled('Update');
        $browser->assertSeeLink('Add New');
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
            '@exportButton' => 'button.export-translation',
            '@importButton' => 'button.import-translation',
            '@createLink' => 'a.create-translation',
            '@updateButton' => 'button.update-translation',
            '@importModal' => 'div.modal.import-modal',
            '@groupFilterTrigger' => '.group-filter .dropdown-trigger > button',
            '@closeModalButton' => 'div.modal.import-modal button.delete',
        ];
    }
}
