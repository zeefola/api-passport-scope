<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $payload = [
            'name' => 'Zainab',
            'email' => 'zeebay@mail.com',
            'password' => 'zee123',
        ];

        $this->json('POST', '/api/register', $payload)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Registration successful',
                'details' => [
                    'name' => 'Zainab'
                ],
            ]);
    }
}
