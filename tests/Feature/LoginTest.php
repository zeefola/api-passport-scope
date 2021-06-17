<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_email_and_password_to_login()
    {
        $this->json('POST', '/api/login')
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.']
                ]
            ]);
    }

    public function test_user_logins_successfully()
    {
        User::factory()->create([
            'email' => 'zeezee@test.com',
            'password' => bcrypt('123456'),
        ]);

        $payload = ['email' => 'zeezee@test.com', 'password' => '123456'];

        $this->json('POST', '/api/login', $payload)
            ->assertStatus(200)
            ->assertJson([
                'message',
                'details' => [
                    'email' => 'zeezee@test.com'
                ],
            ]);
    }
}
