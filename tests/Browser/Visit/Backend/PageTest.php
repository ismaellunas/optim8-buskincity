<?php

namespace Tests\Browser\Visit\Backend;

use App\Models\Page;
use Database\Seeders\PageSeeder;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\PageCreate;
use Tests\Browser\Pages\Backend\PageEdit;
use Tests\Browser\Pages\Backend\PageIndex;
use Tests\Browser\Visit\BaseVisitTestCase;

class PageTest extends BaseVisitTestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function index(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new PageIndex());
        });
    }

    /** @test */
    public function create(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new PageCreate())
                ->assertSee('Title')
                ->assertSee('Slug')

                ->click('@builderTab')
                ->assertSee('General')
                ->assertSee('Columns')
                ->assertSee('Card Text')

                ->click('@settingsTab')
                ->assertSee('Layout')
            ;
        });
    }

    /** @test */
    public function edit(): void
    {
        $this->seed(PageSeeder::class);

        $this->browse(function (Browser $browser) {
            $page = Page::first();

            $browser
                ->loginAs($this->user)
                ->visit(new PageEdit($page))
                ->assertSee('Title')
                ->assertSee('Slug')

                ->click('@builderTab')
                ->assertSee('General')
                ->assertSee('Columns')
                ->assertSee('Card Text')

                ->click('@settingsTab')
                ->assertSee('Layout');
            ;
        });
    }
}
