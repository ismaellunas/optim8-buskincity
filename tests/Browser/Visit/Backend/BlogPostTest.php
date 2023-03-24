<?php

namespace Tests\Browser\Visit\Backend;

use App\Models\Post;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\PostCreate;
use Tests\Browser\Pages\Backend\PostEdit;
use Tests\Browser\Pages\Backend\PostIndex;
use Tests\Browser\Visit\BaseVisitTestCase;
use Database\Seeders\PostSeeder;

class BlogPostTest extends BaseVisitTestCase
{
    /** @test */
    public function index(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new PostIndex())
                ->click('@galleryViewButton')
                ->waitForInertiaSuccess()
                ->assertSee('Showing')

                ->click('@listViewButton')
                ->waitForInertiaSuccess()
                ->assertSee('Showing')

                ->click('@scheduledTabTrigger')
                ->waitForInertiaNavigate()
                ->assertSee('Showing')

                ->click('@draftTabTrigger')
                ->waitForInertiaNavigate()
                ->assertSee('Showing');
        });
    }

    /** @test */
    public function create(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new PostCreate())
                ->click('@seoTabTrigger')
                ->waitForText('Meta title')
                ->assertSee('Meta description');
        });
    }

    /** @test */
    public function edit(): void
    {
        $this->seed(PostSeeder::class);

        $this->browse(function (Browser $browser) {
            $post = Post::first();

            $browser
                ->loginAs($this->user)
                ->visit(new PostEdit($post))
                ->click('@seoTabTrigger')
                ->waitForText('Meta title')
                ->assertSee('Meta description');
        });
    }
}
