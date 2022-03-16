<?php

namespace Tests\Feature\Throttle;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\DatabaseSeeder;
use App\Models\User;
use Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use App\Services\StripeService;

class DonationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        // Arrange
        $this->user = User::factory()->create();
    }

    private function mockStripeService()
    {
        $this->instance(
            StripeService::class,
            Mockery::mock(
                StripeService::class,
                function (MockInterface $mock) {
                    $mock->shouldReceive('getMinimalPaymentWithFee');
                    $mock->shouldReceive('getCurrencyMinimalPayment');
                    $mock->shouldReceive('getCurrencyOptions');
                }
            )
        );
    }

    // References: https://medium.com/@ashleywnj/learning-laravel-small-steps-rate-limiting-testing-9c22165fd65

    /**
     * @test
     */
    public function donationThrottleLimit()
    {
        // Arrange
        $this->mockStripeService();

        // Act
        $response = $this->post(
            route('donations.checkout', $this->user->id),
            [
                'currency' => 'SEK',
                'amount' => '100'
            ]
        );
        $headerRateLimit = $response->headers->get('X-Ratelimit-Limit');

        // Assert
        $this->assertEquals(
            config('constants.throttle.checkout'),
            $headerRateLimit
        );
    }

    /**
     * @test
     */
    public function donationThrottle()
    {
        // Arrange
        $this->mockStripeService();

        // Act
        $max = config('constants.throttle.checkout');
        for ($i = 0; $i < $max + 1; $i++) {
            $response = $this->post(
                route('donations.checkout', $this->user->id),
                [
                    'currency' => 'SEK',
                    'amount' => '100'
                ]
            );
        }

        // Assert
        $response->assertStatus(429);
    }
}
