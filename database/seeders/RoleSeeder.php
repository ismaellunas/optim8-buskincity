<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::factory()->create(['name' => 'Super Administrator']);
        Role::factory()->create(['name' => 'Administrator']);
        Role::factory()->create(['name' => 'Author']);
        Role::factory()->create(['name' => 'Performer']);
    }
}
