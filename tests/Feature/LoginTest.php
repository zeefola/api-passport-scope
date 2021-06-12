<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    public function test_requires_email_and_password_to_login()
    {
        $this->json('POST', '/api/login')
            ->assertStatus(422)
            ->assertJson([
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.']
            ]);
    }

    public function test_user_logins_successfully()
    {
        User::factory()->create([
            'email' => 'zee@test.com',
            'password' => bcrypt('123456'),
        ]);

        $payload = ['email' => 'zee@test.com', 'password' => '123456'];

        $this->json('POST', '/api/login', $payload)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                    'access_token',
                ],
            ]);
    }
}