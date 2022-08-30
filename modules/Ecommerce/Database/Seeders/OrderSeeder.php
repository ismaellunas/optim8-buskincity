<?php

namespace Modules\Ecommerce\Database\Seeders;

use Carbon\Carbon;
use GetCandy\Models\Channel;
use GetCandy\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Enums\OrderLineStatus;
use Modules\Ecommerce\Enums\OrderStatus;
use GetCandy\Base\OrderReferenceGeneratorInterface;
use GetCandy\Models\Order;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $lines = collect();

        $products = Product::orderBy('id', 'DESC')->limit(5)->get();
        $currency = Currency::getDefault();
        $channel = Channel::getDefault();
        $generator = app(OrderReferenceGeneratorInterface::class);

        foreach ($products as $product) {
            $variant = $product->variants->first();

            $lines->push([
                'purchasable_type' => get_class($variant),
                'purchasable_id' => $variant->id,
                'type' => OrderLineStatus::EVENT->value,
                'description' => "",
                'option' => null,
                'identifier' => $variant->sku,
                'unit_price' => 0,
                'unit_quantity' => 0,
                'quantity' => 1,
                'sub_total' => 0,
                'discount_total' => 0,
                'tax_breakdown' => [],
                'tax_total' => 0,
                'total' =>	0,
            ]);

            $order = [
                'user_id' => null,
                'channel_id' => $channel->id,
                'status' => OrderStatus::COMPLETED->value,
                'sub_total' => 0,
                'tax_breakdown' => [],
                'tax_total' => 0,
                'total' => 0,
                'currency_code' => $currency->code,
                'compare_currency_code' => $currency->code,
                'placed_at' => Carbon::now(),
                'meta' => [],
            ];

            $orderModel = Order::factory()->create($order);
            $orderModel->reference = $generator->generate($orderModel);
            $orderModel->save();

            $orderModel->lines()->createMany($lines->toArray());
        }
    }
}
