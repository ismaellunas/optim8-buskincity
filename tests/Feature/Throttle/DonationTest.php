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
                    $mock->shouldReceive('getMinimalPaymentWithFee')->once();
                    $mock->shouldReceive('getCurrencyMinimalPayment')->once();
                    $mock->shouldReceive('getCurrencyOptions')->once();
                }
            )
        );
    }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function donationThrottleLimit()
    {
        // Arrange
        $this->mockStripeService();

        // Act
        // References: https://laracasts.com/discuss/channels/testing/test-rate-limit-throttling-catch-22
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
}
