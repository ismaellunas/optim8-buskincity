<?php

namespace Modules\Ecommerce\Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use GetCandy\Base\OrderReferenceGeneratorInterface;
use GetCandy\Models\Channel;
use GetCandy\Models\Currency;
use GetCandy\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Booking\Entities\Event;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Enums\OrderLineType;
use Modules\Ecommerce\Enums\OrderStatus;
use Modules\Ecommerce\Enums\ProductStatus;

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

        $products = Product::orderBy('id', 'DESC')
            ->whereStatus(ProductStatus::PUBLISHED)
            ->has('eventSchedule.weeklyHours', '>', 3)
            ->limit(5)
            ->get();

        $currency = Currency::getDefault();
        $channel = Channel::getDefault();
        $generator = app(OrderReferenceGeneratorInterface::class);
        $user = User::first();

        foreach ($products as $product) {
            $lines = collect();

            $variant = $product->variants->first();

            $lines->push([
                'purchasable_type' => get_class($variant),
                'purchasable_id' => $variant->id,
                'type' => OrderLineType::EVENT,
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
                'user_id' => $user->id,
                'channel_id' => $channel->id,
                'status' => OrderStatus::COMPLETED,
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

            $orderLine = $orderModel->lines->first();

            Event::factory()->state([
                'schedule_id' => $product->eventSchedule->id,
                'order_line_id' => $orderLine->id,
            ])->create();
        }
    }
}
