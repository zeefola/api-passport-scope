<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_email_password_is_required_to_register()
    {
        $this->json('POST', '/api/register')
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'name' => ['The name field is required.'],
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.']
                ],
            ]);
    }

    public function test_user_registered_successfully()
    {
        $this->withoutExceptionHandling();
        Event::fake();
        $password = bcrypt('zee123');
        $payload = [
            'name' => 'Zainab',
            'email' => 'zeebay@mail.com',
            'password' => $password,
        ];

        $response = $this->json('POST', '/api/register', $payload)
            ->assertStatus(200);

        Event::assertDispatched(UserRegistered::class);

        $response->assertJson([
            'error' => false,
            'msg' => 'Registration Successful. Check your inbox for confirmation',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $payload['name'],
            'email' => $payload['email']
        ]);
    }
}