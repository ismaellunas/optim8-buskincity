<?php

namespace Modules\Ecommerce\Database\Seeders;

use Faker\Factory as Faker;
use GetCandy\FieldTypes\Text;
use GetCandy\FieldTypes\TranslatedText;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;
use GetCandy\Models\TaxClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Ecommerce\Entities\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        $faker = Faker::create();

        $productType = ProductType::where('name', 'Event')->first();

        $taxClass = TaxClass::getDefault();

        for ($i = 0; $i < 16; $i++) {
            $product = Product::create([
                'product_type_id' => $productType->id,
                'status' => '',
                'attribute_data' => [
                    'name' => new TranslatedText(collect([
                        'en' => new Text($faker->sentence(3)),
                    ])),
                    'description' => new TranslatedText(collect([
                        'en' => new Text($faker->text()),
                    ])),
                ],
            ]);

            ProductVariant::create([
                'product_id' => $product->id,
                'tax_class_id' => $taxClass->id,
                'purchasable' => 'always',
                'shippable' => false,
                'stock' => 0,
                'backorder' => 0,
                'sku' => 'EVENT-'.$product->id,
            ]);
        }
    }
}
