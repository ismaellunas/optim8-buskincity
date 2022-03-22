<?php

namespace Tests\Feature\Throttle;

use App\Models\User;
use App\Services\StripeService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class StripeDonationCheckoutTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $user;
    private $baseRouteName = 'donations.checkout';

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
                    $mock->shouldReceive('isEnabled')->andReturn(true);
                }
            )
        );
    }

    private function getDonationData(): array
    {
        return [
            'currency' => 'SEK',
            'amount' => '100'
        ];
    }

    /**
     * @test
     */
    public function accessHeaderRateLimitSameWithLimiter()
    {
        // Arrange
        $this->mockStripeService();

        // Act
        $response = $this->post(
            route($this->baseRouteName, $this->user->id),
            $this->getDonationData()
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
    // public function accessMoreThanRateLimiterWillTriggerError429()
    // {
    //     // Arrange
    //     $this->mockStripeService();

    //     // Act
    //     $max = config('constants.throttle.checkout');
    //     for ($i = 0; $i < $max + 1; $i++) {
    //         $response = $this->post(
    //             route($this->baseRouteName, $this->user->id),
    //             $this->getDonationData()
    //         );
    //         // dd($response-);
    //     }

    //     // Assert
    //     $response->assertStatus(429);
    // }

    /**
     * @test
     */
    public function rateLimiterDoesNotAffectOtherSessionId() {
        // Arrange
        $this->mockStripeService();
        $sessionId = Str::random(20);

        // Act
        $max = config('constants.throttle.checkout');
        for ($i = 0; $i < $max; $i++) {
            $response = $this->withSession(['id' => $sessionId])
                ->post(
                    route($this->baseRouteName, $this->user->id),
                    $this->getDonationData()
                );
        }

        $sessionId = Str::random(20);
        $response = $this->withSession(['id' => $sessionId])
            ->post(
                route($this->baseRouteName, $this->user->id),
                $this->getDonationData(),
            );

        // Assert
        $response->assertStatus(302);
    }
}
