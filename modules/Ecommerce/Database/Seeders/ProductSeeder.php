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
        [ // ----- Sweden
            'title'   => 'Ångbåtsmuseum',
            'latlng'  => [62.95209940865782, 14.502892558054729],
            'address' => 'Kyrkvägen 11 B, 845 94 Hackås, Sweden',
            'country_code' => 'SE',
        ], [
            'title'   => 'Rickards Traktormuseum',
            'latlng'  => '63.29232066995999, 16.807274854705817',
            'address' => 'Helgumsbyn 155, 882 93 Helgum, Sweden',
            'country_code' => 'SE',
            'phone'   => '+46702590537',
        ], [
            'title'   => 'Missionsladan',
            'latlng'  => [ 63.36136537145666, 13.34108846987545 ],
            'address' => 'Hålland 753, 830 10 Undersåker, Sweden',
            'country_code' => 'SE',
            'phone'   => '+4664731010',
        ], [
            'title'   => 'ArveMuseet',
            'latlng'  => [ 63.30712990833497, 14.060692933287143 ],
            'address' => 'Södra Arvesund 516, 830 02 Mattmar, Sweden',
            'country_code' => 'SE',
            'phone'   => '+4664044168',
        ], [
            'title'   => 'Dunderklumpen',
            'latlng'  => [ 63.87515608821116, 15.543847170853226 ],
            'address' => 'HEMBYGDSGÅRDEN 1003, 833 35 Strömsund, Sweden',
            'country_code' => 'SE',
        ], [ //----- Norway,
            'title'   => 'Levanger Museum',
            'latlng'  => [ 63.75031163512022, 11.304497840490294 ],
            'address' => 'Gimlevegen 1, 7600 Levanger, Norway',
            'country_code' => 'NO',
            'phone'   => '+4799125333',
        ], [
            'title'   => 'Stiklestad Cultural History Museum',
            'latlng'  => [ 63.81037873779975, 11.576409450710743 ],
            'address' => '7650 Verdal, Norway',
            'country_code' => 'NO',
        ], [
            'title'   => 'Mosvik museum og historielag',
            'latlng'  => [ 63.83478510580997, 11.005005241437246 ],
            'address' => 'Vimpelivegen 2, 7690 Mosvik, Norway',
            'country_code' => 'NO',
            'phone'   => '+4741517633',
        ], [ //----- Denmark,
            'title'   => 'Museum of Danish Resistance',
            'latlng'  => [ 55.69949359665636, 12.600613532128182 ],
            'address' => 'Esplanaden 13, 1263 København, Denmark',
            'country_code' => 'DK',
            'phone'   => '+4541206080',
        ], [
            'title'   => 'Barbie Museum',
            'latlng'  => [ 55.70571403154807, 12.524343033412054 ],
            'address' => 'Vibevej 52, 2400 København, Denmark',
            'country_code' => 'DK',
            'phone'   => '+4521481827',
        ], [ //----- Germany,
            'title'   => 'Altonaer Museum',
            'latlng'  => [ 53.58622158941728, 9.941411206151681 ],
            'address' => 'Museumstraße 23, 22765 Hamburg, Germany',
            'country_code' => 'DE',
            'phone'   => '+49404281350',
        ], [
            'title'   => 'Museum Lüneburg',
            'latlng'  => [ 53.281251947664416, 10.38316076099741 ],
            'address' => 'Willy-Brandt-Straße 1, 21335 Lüneburg, Germany',
            'country_code' => 'DE',
            'phone'   => '+4941317206580',
        ],
    ];

    public function run()
    {
        Model::unguard();

        for ($i = 0; $i < 5; $i++) {
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
