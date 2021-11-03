<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Passport;
use Tests\TestCase;
use App\Models\User;
use App\Events\UserActivated;
use App\Events\UserRegistered;
use App\Events\UserChangePassword;

class RegisterTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_name_email_password_is_required_to_register()
    {
        $this->json('POST', '/api/register')
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'name' => ['The name field is required.'],
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],
                    "username" => [
                        "The username field is required."
                    ],
                    "phone_number" => [
                        "The phone number field is required."
                    ],
                ],
            ]);
    }

    public function test_user_registered_successfully()
    {
        $this->withoutExceptionHandling();
        Event::fake();
        // $password = bcrypt('zee123');
        $name = $this->faker->name();
        $username = $this->faker->userName;
        $phone_number = '081' . (string)$this->faker->numerify("########");
        $email = $this->faker->unique()->safeEmail;
        $password = $this->faker->password(8);

        $payload = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'username' => $username,
            'phone_number' => $phone_number,
        ];

        $response = $this->json('POST', '/api/register', $payload)
            ->assertStatus(200);

        Event::assertDispatched(UserRegistered::class);

        $response->assertJson([
            'error' => false,
            'msg' => 'Registration Successful. Check your inbox for confirmation',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'phone_number' => $phone_number,
        ]);
    }

    /**
     * Test confirm account after registration using confirmation token
     *
     * @return void
     */
    public function testConfirmToken()
    {
        Event::fake();

        $user = User::factory()->create();
        // echo $user->email;
        $response = $this->json('POST', '/api/confirm-code', [
            'email' => $user->email,
            'confirm_code' => $user->remember_token
        ]);

        Event::assertDispatched(UserActivated::class);

        $response->assertJson([
            "error" => false,
            "msg" => "Account has been activated successfully."
        ]);
    }

    /**
     * Test resend confirmation code for confirming registration
     *
     * @return void
     */
    public function testResendConfirmationCode()
    {
        Event::fake();

        $user = User::factory()->create();

        $response = $this->json('POST', '/api/resend-confirmation-code', [
            'email' => $user->email
        ]);

        Event::assertDispatched(UserRegistered::class);

        $response->assertJson([
            "error" => false,
            "msg" => "Account activation code has been sent."
        ]);
    }

    /**
     * Test reset password, email does not exist
     *
     * @return void
     */
    public function testResetPasswordAccountDoesNotExist()
    {
        User::factory()->create();
        $email = $this->faker->email();

        $response = $this->json('POST', '/api/reset-password', [
            "email" => $email
        ]);

        $response->assertJson([
            "error" => true,
            "msg" => "Email is not associated with any account."
        ]);
    }

    /**
     * Test reset password
     *
     * @return void
     */
    public function testResetPassword()
    {
        Event::fake();

        $user = User::factory()->create();

        $response = $this->json('POST', '/api/reset-password', [
            "email" => $user->email
        ]);

        Event::assertDispatched(UserChangePassword::class);

        $response->assertJson([
            "error" => false,
            "msg" => "A new password has been sent to your email"
        ]);
    }

    /**
     * Test change password, confirm password
     *
     * @return void
     */
    public function testChangePasswordConfirmationError()
    {
        $user = User::factory()->create([
            "active" => true,
            "remember_token" => ""
        ]);

        Passport::actingAs($user);

        $password = $this->faker->password(8);
        $confirmpassword = $this->faker->password(9);

        $response = $this->json('POST', '/api/update-password', [
            "oldpassword" => "secret",
            "password" => $password,
            "confirmpassword" => $confirmpassword
        ]);

        $response->assertJson([
            "error" => true,
            "msg" => "Incorrect confirm password"
        ]);
    }

    /**
     * Test change password, incorrect old password
     *
     * @return void
     */
    public function testChangePasswordIncorrectOldPasswordError()
    {
        $user = User::factory()->create([
            "active" => true,
            "remember_token" => ""
        ]);

        Passport::actingAs($user);

        $password = $this->faker->password(8);

        $response = $this->json('POST', '/api/update-password', [
            "oldpassword" => "secrets",
            "password" => $password,
            "confirmpassword" => $password
        ]);

        $response->assertJson([
            "error" => true,
            "msg" => "Old password is incorrect"
        ]);
    }

    /**
     * Test change password
     *
     * @return void
     */
    public function testChangePassword()
    {
        $user = User::factory()->create([
            "active" => true,
            "remember_token" => ""
        ]);

        Passport::actingAs($user);

        $password = $this->faker->password(8);

        $response = $this->json('POST', '/api/update-password', [
            "oldpassword" => "secret",
            "password" => $password,
            "confirmpassword" => $password
        ]);

        $response->assertJson([
            "error" => false,
            "msg" => "Password changed successfully"
        ]);
    }
}