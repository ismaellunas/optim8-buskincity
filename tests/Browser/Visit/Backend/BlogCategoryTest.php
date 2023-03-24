<?php

namespace Tests\Browser\Visit\Backend;

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\BlogCategoryCreate;
use Tests\Browser\Pages\Backend\BlogCategoryEdit;
use Tests\Browser\Pages\Backend\BlogCategoryIndex;
use Tests\Browser\Visit\BaseVisitTestCase;

class BlogCategoryTest extends BaseVisitTestCase
{
    /** @test */
    public function index(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new BlogCategoryIndex())
            ;
        });
    }

    /** @test */
    public function create(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new BlogCategoryCreate())
            ;
        });
    }

    /** @test */
    public function edit(): void
    {
        $this->seed(CategorySeeder::class);

        $this->browse(function (Browser $browser) {
            $category = Category::first();

            $browser
                ->loginAs($this->user)
                ->visit(new BlogCategoryEdit($category))
            ;
        });
    }
}
