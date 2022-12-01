<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Language;
use Database\Seeders\LanguageTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(LanguageTestSeeder::class);
    }

    public function test_name_and_email_cannot_be_updated()
    {
        // ARRANGE
        $originalFirstName = $this->faker->firstName();
        $originalLastName = $this->faker->lastName();
        $originalEmail  = $this->faker->email();

        $language = Language::factory()->create();

        $this->actingAs($user = User::factory()->create([
            'first_name' => $originalFirstName,
            'last_name' => $originalLastName,
            'email' => $originalEmail,
        ]));

        // ACT
        $response = $this->put('/user/profile-information', [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->email(),
            'language_id' => $language->id,
        ]);

        // ASSERT
        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertSessionHasNoErrors();

        $this->assertEquals($originalFirstName, $user->fresh()->first_name);
        $this->assertEquals($originalLastName, $user->fresh()->last_name);
        $this->assertEquals($originalEmail, $user->fresh()->email);
    }
}
