<?php

namespace Database\Seeders;

use App\Models\{
    Language,
    Permission,
    Role,
    User,
    Setting,
};
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class UserAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->setPermissionToRoles();

        $englishId = Language::where('code', 'en')->value('id');
        $countryCode = Setting::key('default_country')->value('value');
        $domain = config('constants.domain');

        $superAdminUser = User::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Administrator',
            'email' => 'super.administrator@'.$domain,
            'country_code' => $countryCode,
            'language_id' => $englishId,
        ]);

        $superAdminUser->assignRole(config('permission.super_admin_role'));

        $adminUser = User::factory()
            ->create([
                'first_name' => 'Admin',
                'last_name' => 'Administrator',
                'email' => 'admin@'.$domain,
                'country_code' => $countryCode,
                'language_id' => $englishId,
            ]);

        $adminUser->assignRole('Administrator');

        $performers = User::factory()
            ->count(2)
            ->state(new Sequence(
                [
                    'first_name' => 'Dan',
                    'last_name' => 'Rice',
                    'email' => 'dan.rice@'.$domain,
                    'country_code' => $countryCode,
                    'language_id' => $englishId,
                ],
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@'.$domain,
                    'country_code' => $countryCode,
                    'language_id' => $englishId,
                ],
            ))
            ->create();

        foreach ($performers as $performer) {
            $performer->assignRole('Performer');
        }
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
    }
}
