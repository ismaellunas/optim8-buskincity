<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Target behavior for FR-BOOK-5 (T8.1).
 *
 * Expected to FAIL while admin ProductEvent routes still exist.
 *
 * @group events-overhaul
 */
class ProductEventRemovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Modules\Ecommerce\Database\Seeders\DefaultSeeder::class);

        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureLoginFromAdminLoginRoute::class,
            \App\Http\Middleware\UserEmailIsVerified::class,
            \App\Http\Middleware\VerifyModule::class,
        ]);
    }

    /** @test */
    public function admin_product_event_routes_are_removed(): void
    {
        $this->assertFalse(Route::has('admin.booking.products.product-events.store'));
        $this->assertFalse(Route::has('admin.booking.products.product-events.records'));
        $this->assertFalse(Route::has('booking.events.show'));
    }

    /** @test */
    public function product_events_table_is_dropped(): void
    {
        $this->assertFalse(
            Schema::hasTable('product_events'),
            'product_events table should be removed in T8.1'
        );
    }
}
