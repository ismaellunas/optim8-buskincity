<?php

namespace Tests\Browser\Visit\Backend;

use Database\Seeders\CountryTestSeeder;
use Database\Seeders\GlobalOptionSeeder;
use Database\Seeders\UserAndPermissionSeeder;
use Laravel\Dusk\Browser;
use Modules\Booking\Database\Seeders\BookingDatabaseSeeder;
use Modules\Ecommerce\Database\Seeders\EcommerceDatabaseSeeder;
use Tests\Browser\Pages\Backend\BookingSetting;
use Tests\Browser\Visit\BaseVisitTestCase;

class BookingSettingTest extends BaseVisitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(GlobalOptionSeeder::class);
        $this->seed(CountryTestSeeder::class);
        $this->seed(UserAndPermissionSeeder::class);
        $this->seed(EcommerceDatabaseSeeder::class);
        $this->seed(BookingDatabaseSeeder::class);
    }

    /** @test */
    public function index(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit(new BookingSetting())
                ->click('@checkInTabTrigger')
                ->waitForTextIn('@checkInForm', __('Available check-in before'))
                ->assertSee(__('Available check-in before'))
                ->click('@emailTabTrigger')
                ->waitForTextIn('@emailForm', __('New booking'))
                ->assertSee(__('New booking'))
            ;
        });
    }
}
