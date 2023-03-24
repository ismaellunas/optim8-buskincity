<?php

namespace Tests\Browser\Visit\Backend;

use App\Models\Role;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\RoleCreate;
use Tests\Browser\Pages\Backend\RoleEdit;
use Tests\Browser\Pages\Backend\RoleIndex;
use Tests\Browser\Visit\BaseVisitTestCase;

class RoleTest extends BaseVisitTestCase
{
    /** @test */
    public function index(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new RoleIndex());
        });
    }

    /** @test */
    public function create(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new RoleCreate());
        });
    }

    /** @test */
    public function edit(): void
    {
        $this->browse(function (Browser $browser) {
            $user = Role::first();
            $browser
                ->loginAs($this->user)
                ->visit(new RoleEdit($user));
        });
    }
}
