<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Events\UserRegistered;

class AuthRepository
{
    public function register($data)
    {
        //Create a record and send response to the controller
        $user = User::create(
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'scopes' => ['user', 'all', 'products']
            ]
        );

        $email_data = [
            'username' => $data['name'],
            'mailTo' => $data['email'],
            'subject' => 'Successful Registration',
            'mail_body' => 'You\'re getting this mail because you successfully registered on our platform',
            'button_name' => 'Click to Login',
            'button_link' => 'http://localhost/api/login'
        ];

        event(new UserRegistered($email_data));

        return [
            'message' => 'Registration Successful',
            'mail' => 'Mail sent check your inbox',
            'details' => $user,
        ];
    }

    public function login($data)
    {
        $user_email = $data['email'];
        $user_password = $data['password'];

        //Get User's Record
        $user = User::where('email', $user_email)->first();

        // Compare Db with Request Data
        if (!$user) {
            return [
                'message' => 'User not found',
                'status' => 'failed',
            ];
        }

        if (!Hash::check($user_password, $user->password)) {
            return [
                'message' => 'Invalid Credential',
                'status' => 'failed',
            ];
        }

        //Create an access token for the user
        $accessToken = $user->createToken('accessToken', $user->scopes)->accessToken;

        return [
            'message' => 'Login Successful',
            'details' => $user,
            'access_token' => $accessToken,
        ];
    }
}