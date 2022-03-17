<?php

namespace Database\Factories;

use App\Models\PaymentWebhook;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class PaymentWebhookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $eventTypes = [
            'payment_intent.succeeded',
            'charge.captured',
            'checkout.session.completed',
        ];

        return [
            'payment_method' => PaymentWebhook::PAYMENT_METHOD_STRIPE,
            'event_type' => Arr::random($eventTypes),
            'data' => json_encode([
                'id' => "evt_".$this->faker->uuid(),
                'data' => [
                    'object' => [
                        'id' => "pi_".$this->faker->uuid(),
                    ]
                ],
            ])
        ];
    }
}
