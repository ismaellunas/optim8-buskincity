<?php

namespace Tests\Browser\Visit\Backend;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\UserCreate;
use Tests\Browser\Pages\Backend\UserEdit;
use Tests\Browser\Pages\Backend\UserIndex;
use Tests\Browser\Visit\BaseVisitTestCase;

class UserTest extends BaseVisitTestCase
{
    /** @test */
    public function index(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new UserIndex());
        });
    }

    /** @test */
    public function create(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new UserCreate());
        });
    }

    /** @test */
    public function edit(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::first();
            $browser
                ->loginAs($this->user)
                ->visit(new UserEdit($user));
        });
    }
}
