<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthRepository
{
    public function register($data)
    {
        //Create a record and send response to the controller
        return User::create(
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'scopes' => ['user', 'all', 'products']
            ]
        );
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
