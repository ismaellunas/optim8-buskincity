<?php

namespace Database\Seeders;

use App\Models\{
    Country,
    Language,
    Permission,
    Role,
    Setting,
    User,
};
use App\Services\GlobalOptionService;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class UserAndPermissionSeeder extends Seeder
{
    private $languageId = null;
    private $countryCode = null;
    private $domain = null;

    public function __construct()
    {
        $this->languageId = Language::where('code', 'en')->value('id');
        $this->countryCode = Setting::key('default_country')->value('value');
        $this->domain = config('constants.domain');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->setPermissionToRoles();

        $superAdminUser = User::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Administrator',
            'email' => 'super.administrator@'.$this->domain,
            'language_id' => $this->languageId,
        ]);

        $superAdminUser->setMeta('country', $this->countryCode);
        $superAdminUser->saveMetas();

        $superAdminUser->assignRole(config('permission.super_admin_role'));

        $adminUser = User::factory()
            ->create([
                'first_name' => 'Admin',
                'last_name' => 'Administrator',
                'email' => 'admin@'.$this->domain,
                'language_id' => $this->languageId,
            ]);

        $adminUser->setMeta('country', $this->countryCode);
        $adminUser->saveMetas();

        $adminUser->assignRole('Administrator');
    }

    private function setPermissionToRoles(): void
    {
        // Wildcard permissions
        $wildcardPermissions = Permission::where('name', 'LIKE', '%.*')->get();

        $administratorRole = Role::whereName('Administrator')->first();
        $administratorRole->syncPermissions($wildcardPermissions);

        // System Permissions
        $systemPermissions = Permission::where('name', 'LIKE', 'system.%')->get();

        foreach ($systemPermissions as $permission) {
            $administratorRole->givePermissionTo($permission);
        }

        // Payment management Permissions
        $performerRole = Role::whereName('Performer')->first();

        $performerRole->syncPermissions([
            'payment.management',
            'public_page.profile',
        ]);

        $authorRole = Role::whereName('Author')->first();
        $authorRole->syncPermissions([
            'system.dashboard',
        ]);
    }
}
