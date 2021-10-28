<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Passport;

class LoginTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
        $this->withoutExceptionHandling();
        $user = User::factory()->create([
            'email' => 'zeezee@test.com',
            'password' => bcrypt('123456'),
        ]);

        Passport::actingAs($user);

        $this->json('POST', '/api/login', [
            'email' => $user->email,
            'password' => '123456'
        ])->assertJson([
            'error' => false,
            'msg' => 'Login Successful',
        ]);

        $this->assertAuthenticatedAs($user);
    }
}