<?php

namespace Modules\Ecommerce\Database\Seeders;

use GetCandy\Models\Channel;
use GetCandy\Models\Currency;
use GetCandy\Models\Language;
use GetCandy\Models\ProductType;
use GetCandy\Models\TaxClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

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

        Channel::create([
            'name'    => 'Webstore',
            'handle'  => 'webstore',
            'default' => true,
            'url'     => 'localhost',
        ]);

        Currency::create([
            'code'           => 'USD',
            'name'           => 'US Dollar',
            'exchange_rate'  => 1,
            'decimal_places' => 2,
            'default'        => true,
            'enabled'        => true,
        ]);
    }
}
