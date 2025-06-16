<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules = [
            [
                'name' => 'FormBuilder',
                'title' => 'form_builder::terms.form_builder',
                'navigations' => app('\Modules\FormBuilder\ModuleService')->defaultNavigations(),
                'is_manageable' => true,
                'order' => 0,
            ],
            [
                'name' => 'Booking',
                'title' => ':booking_term.booking',
                'navigations' => app('\Modules\Booking\ModuleService')->defaultNavigations(),
                'is_manageable' => true,
                'order' => 1,
            ],
            [
                'name' => 'Space',
                'title' => ':space_term.space',
                'navigations' => app('\Modules\Space\ModuleService')->defaultNavigations(),
                'is_manageable' => true,
                'order' => 2,
            ],
            [
                'name' => 'Ecommerce',
                'title' => ':ecommerce_term.ecommerce',
                'navigations' => [],
                'is_manageable' => false,
            ],
        ];

        foreach ($modules as $module) {
            Module::factory()->state($module)->activated()->create();
        }
    }
}
