<?php

namespace Modules\Ecommerce\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use GetCandy\Models\ProductType;
use GetCandy\Models\TaxClass;
use GetCandy\Models\Language;

class DefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        ProductType::create([
            'name' => 'Event',
        ]);

        Language::create([
            'code' => 'en',
            'name' => 'English',
            'default' => true,
        ]);

        TaxClass::create([
            'name' => 'Default Tax Class',
            'default' => true,
        ]);
    }
}
