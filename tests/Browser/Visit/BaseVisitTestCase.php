<?php

namespace Tests\Browser\Visit;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

abstract class BaseVisitTestCase extends DuskTestCase
{
    use DatabaseMigrations;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(SettingSeeder::class);
        $this->seed(LanguageSeeder::class);
        $this->withoutVite();

        // Arrange
        $this->user = User::factory()->create();
        $this->user->assignRole(config('permission.super_admin_role'));
    }

    protected function storeConsoleLogsFor($browsers)
    {
        $browsers->each(function ($browser) {
            if (in_array($browser->driver->getCapabilities()->getBrowserName(), Browser::$supportsRemoteLogs)) {
                $console = $browser->driver->manage()->getLog('browser');
                $this->assertEmpty($console, "Browser contains console log(s)");
            }
        });

        parent::storeConsoleLogsFor($browsers);
    }
}
