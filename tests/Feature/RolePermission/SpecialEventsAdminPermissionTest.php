<?php

namespace Tests\Feature\RolePermission;

use App\Enums\UserRole;
use App\Models\City;
use App\Models\Permission;
use App\Models\User;
use App\Services\UserService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Space\Entities\Space;
use Tests\TestCase;

/**
 * Phase 2 (T2.1/T2.2): Special Events Admin role.
 *
 * Covers: role is seeded & assignable, has only its scoped permissions
 * (NOT system.dashboard — OQ14), is city-scoped via user_scope (many-per-city,
 * NOT the legacy city_user pivot), and shows up in the admin role dropdown.
 */
class SpecialEventsAdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    private string $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->role = config('permission.role_names.special_events_admin');
    }

    /** @test */
    public function the_role_is_seeded_and_assignable(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->role);

        $this->assertTrue($user->fresh()->hasRole($this->role));
        $this->assertSame('special_events_admin', UserRole::SPECIAL_EVENTS_ADMIN->value);
    }

    /** @test */
    public function it_has_its_scoped_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->role);

        $this->assertTrue($user->can('special_events.manage'));
        $this->assertTrue($user->can('product.add'));
        $this->assertTrue($user->can('public_page.profile'));
    }

    /** @test */
    public function it_does_not_have_full_dashboard_access(): void
    {
        // OQ14: City/Special-Events admins must NOT have system.dashboard.
        Permission::firstOrCreate(['name' => 'system.dashboard', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole($this->role);

        $this->assertFalse($user->can('system.dashboard'));
    }

    /** @test */
    public function assigning_cities_writes_user_scope_not_city_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->role);

        $cityA = City::factory()->create();
        $cityB = City::factory()->create();

        $user->syncScopeCities($this->role, [$cityA->id, $cityB->id]);

        // user_scope holds both cities for this role...
        $scoped = $user->scopeIdsFor($this->role, 'city');
        sort($scoped);
        $this->assertSame([$cityA->id, $cityB->id], $scoped);
        $this->assertTrue($user->inScope('city', $cityA->id, $this->role));

        // ...and the legacy city_user pivot is untouched (SE admin is user_scope only).
        $this->assertCount(0, $user->adminCities()->get());
    }

    /** @test */
    public function many_special_events_admins_can_share_a_city(): void
    {
        $city = City::factory()->create();

        $first = User::factory()->create();
        $first->assignRole($this->role);
        $first->syncScopeCities($this->role, [$city->id]);

        $second = User::factory()->create();
        $second->assignRole($this->role);
        $second->syncScopeCities($this->role, [$city->id]);

        $this->assertTrue($first->inScope('city', $city->id, $this->role));
        $this->assertTrue($second->inScope('city', $city->id, $this->role));
    }

    /** @test */
    public function assigned_scope_cities_resolves_from_user_scope(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->role);

        $city = City::factory()->create();
        $user->syncScopeCities($this->role, [$city->id]);

        $cities = $user->assignedScopeCities();

        $this->assertCount(1, $cities);
        $this->assertEquals($city->id, $cities->first()->id);
    }

    /** @test */
    public function the_role_appears_in_the_admin_role_options(): void
    {
        $options = app(UserService::class)->getRoleOptions();

        $this->assertTrue(
            $options->contains(fn ($option) => $option['value'] === 'Special Events Admin'),
            'Special Events Admin should be selectable in the user role dropdown.'
        );
    }

    /** @test */
    public function special_events_admin_can_browse_locations(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->role);

        $this->assertTrue($user->can('viewAny', Space::class));
        $this->assertTrue($user->can('create', Space::class));
    }

    /** @test */
    public function special_events_admin_can_open_booking_products_index(): void
    {
        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureLoginFromAdminLoginRoute::class,
            \App\Http\Middleware\VerifyModule::class,
        ]);

        $city = City::factory()->create();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($this->role);
        $user->syncScopeCities($this->role, [$city->id]);

        $this->actingAs($user)
            ->get(route('admin.booking.products.index'))
            ->assertOk();
    }

    /** @test */
    public function special_events_admin_login_redirects_to_booking_products(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret-password'),
        ]);
        $user->assignRole($this->role);

        $this->post(route('admin.login.attempt'), [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.booking.products.index'));
    }
}
