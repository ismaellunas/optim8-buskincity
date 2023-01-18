<?php

namespace Modules\Ecommerce\Database\Seeders;

use App\Models\Role;
use Faker\Factory as Faker;
use Faker\Generator as FakerGenerator;
use GetCandy\FieldTypes\Text;
use GetCandy\FieldTypes\TranslatedText;
use GetCandy\Models\ProductType;
use GetCandy\Models\ProductVariant;
use GetCandy\Models\TaxClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Modules\Booking\Entities\Schedule;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Enums\ProductStatus;

class ProductSeeder extends Seeder
{
    private $faker;

    private $productType;

    private $performerRole;

    private $taxClass;

    private $publishedData = [
        [ // ----- Germany
            'title' => "Pergamonmuseum",
            'address' => "Bodestraße 1-3, 10178 Berlin, Germany",
            'latlng' => [ 52.52119252486984, 13.396902451332059 ],
            'country_code' => 'DE',
        ], [
            'title' => "Brandenburg Gate",
            'address' => "Pariser Platz, 10117, G98H+G3",
            'latitude' => [ 52.51733698723363, 13.377799115385956 ],
            'country_code' => 'DE',
        ], [
            'title' => "Glyptothek",
            'address' => "Königsplatz 3, 80333",
            'latlng' => [ 48.14661473948528, 11.56567738041144 ],
            'country_code' => 'DE',
        ], [
            'title' => "Deutsches Museum",
            'address' => "Museumsinsel 1, 80538 4HHM+W9",
            'latitude' => [ 48.13495937007051, 11.5840630265014 ],
            'country_code' => 'DE',
        ], [ // ----- Sweden
            'title' => "Vasa Museum",
            'address' => "Galärvarvsvägen 14, 115 21 83HR+6H",
            'latlng' => [ 59.3286437970886, 18.0915704926579 ],
            'country_code' => 'SE',
        ],
        [
            'title' => "Nobel Prize Museum",
            'address' => "Stortorget 2, 103 16 83GC+48",
            'latlng' => [ 59.3262018218294, 18.071118483826 ],
            'country_code' => 'SE',
        ], [ //----- Netherlands,
            'title' => "Stedelijk Museum Amsterdam",
            'address' => "Museumplein 10, 1071 DJ, 9V5H+6W",
            'latlng' => [ 52.3584304500091, 4.87989154112885 ],
            'country_code' => 'NL',
        ], [
            'title' => "Rijksmuseum",
            'address' => "Museumstraat 1, 1071 XX 9V5P+X3",
            'latlng' => [ 52.3607373575951, 4.88530846000091 ],
            'country_code' => 'NL',
        ],
    ];

    public function run()
    {
        Model::unguard();

        for ($i = 0; $i < 2; $i++) {
            $product = $this->createProduct(ProductStatus::DRAFT->value);

            $this->createProductVariant($product);

            $this->createProductMeta($product);

            $this->createSchedule($product);
        }

        foreach ($this->publishedData as $data) {
            $product = $this->createProduct(
                ProductStatus::PUBLISHED->value,
                $data['title']
            );

            $this->createProductVariant($product);

            $explodedAddress = array_map('trim', explode(',', (Arr::get($data, 'address'))));

            end($explodedAddress);

            $city = collect(explode(' ', prev($explodedAddress)))
                ->filter(fn ($str) => !is_numeric($str))
                ->implode(' ');

            $meta = [
                'locations' => [
                    [
                        'latitude' => Arr::get($data, 'latlng.0'),
                        'longitude' => Arr::get($data, 'latlng.1'),
                        'address' => Arr::get($data, 'address'),
                        'country_code' => Arr::get($data, 'country_code'),
                        'city' => $city,
                    ],
                ],
            ];

            $this->createProductMeta($product, $meta);

            $this->createSchedule($product);
        }
    }

    private function getTaxClass(): TaxClass
    {
        if (is_null($this->taxClass)) {
            $this->taxClass = TaxClass::getDefault();
        }

        return $this->taxClass;
    }

    private function getPerformerRole(): Role
    {
        if (is_null($this->performerRole)) {
            $this->performerRole = Role::findByName('Performer');
        }

        return $this->performerRole;
    }

    private function getProductType(): ProductType
    {
        if (is_null($this->productType)) {
            $this->productType = ProductType::where('name', 'Event')->first();
        }

        return $this->productType;
    }

    private function getFaker(): FakerGenerator
    {
        if (is_null($this->faker)) {
            $this->faker = Faker::create();
        }

        return $this->faker;
    }

    private function createProduct($status, $name = null)
    {
        return Product::create([
            'product_type_id' => $this->getProductType()->id,
            'status' => $status,
            'attribute_data' => [
                'name' => new TranslatedText(collect([
                    'en' => new Text($name ?? $this->getFaker()->sentence(3)),
                ])),
                'description' => new TranslatedText(collect([
                    'en' => new Text($this->getFaker()->realText()),
                ])),
                'short_description' => new TranslatedText(collect([
                    'en' => new Text($this->getFaker()->text()),
                ])),
            ],
        ]);
    }

    private function createProductVariant($product)
    {
        return ProductVariant::create([
            'product_id' => $product->id,
            'tax_class_id' => $this->getTaxClass()->id,
            'purchasable' => 'always',
            'shippable' => false,
            'stock' => 0,
            'backorder' => 0,
            'sku' => 'EVENT-'.$product->id,
        ]);
    }

    private function createProductMeta(&$product, $meta = [])
    {
        $default_meta = [
            'roles' => [$this->getPerformerRole()->id],
            'duration' => 30,
            'duration_unit' => 'minute',
            'bookable_date_range_type' => 'calendar_days_into_the_future',
            'bookable_date_range' => 60,
        ];

        $meta = array_merge($default_meta, $meta);

        $product->setMeta($meta);
        $product->save();
    }

    private function createSchedule($product)
    {
        Schedule::factory()->state([
            'schedulable_type' => Product::class,
            'schedulable_id' => $product->id,
        ])->create();
    }
}
