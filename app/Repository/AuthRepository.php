<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthRepository
{
    public function register()
    {
        //Create a record and send response to the controller
        $user = User::create(
            [
                'name' => request()->name,
                'email' => request()->email,
                'password' => bcrypt(request()->password),
                'scopes' => ['user', 'all', 'products']
            ]
        );
        return response()->json([
            'message' => 'Registration successful',
            'details' => $user
        ]);
    }

    public function login()
    {
        $user_email = request()->email;
        $user_password = request()->password;

        //Get User's Record
        $user = User::where('email', $user_email)->first();

        // Compare Db with Request Data
        if (!$user) {
            return response()->json(['message' => 'Invalid Credential']);
        }

        if (!Hash::check($user_password, $user->password)) {
            return response()->json(['message' => 'Invalid Credential']);
        }

        if ($user) {
            if (Hash::check($user_password, $user->password)) {
                //Create an access token for the user
                $accessToken = $user->createToken('accessToken', $user->scopes)->accessToken;

                return response()->json([
                    'message' => 'Login Successful',
                    'details' => $user,
                    'access_token' => $accessToken
                ]);
            }
        }
    }
}