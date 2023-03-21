<?php

namespace Modules\Ecommerce\Database\Seeders;

use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\ProductType;
use Lunar\Models\TaxClass;
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
