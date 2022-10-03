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

        $this->populateUserPerformer();
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

    private function populateUserPerformer()
    {
        $disciplines = app(GlobalOptionService::class)->getDisciplineOptions();
        $countries = Country::select('alpha2')->get();

        $performers = User::factory()
            ->count(2)
            ->state(new Sequence(
                [
                    'first_name' => 'Dan',
                    'last_name' => 'Rice',
                    'email' => 'dan.rice@'.$this->domain,
                    'language_id' => $this->languageId,
                ],
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@'.$this->domain,
                    'language_id' => $this->languageId,
                ],
            ))
            ->create();

        foreach ($performers as $performer) {
            $performer->setMeta('country', $this->countryCode);
            $performer->setMeta('discipline', $disciplines->random()['id']);
            $performer->saveMetas();

            $performer->assignRole('Performer');
        }

        $anotherPerformer = User::factory()
            ->count(10)
            ->state(new Sequence(
                fn () => ['language_id' => $this->languageId],
            ))
            ->create();

        foreach ($anotherPerformer as $performer) {
            $performer->setMeta('country', $countries->random()->alpha2);
            $performer->setMeta('discipline', $disciplines->random()['id']);
            $performer->saveMetas();

            $performer->assignRole('Performer');
        }
    }
}
