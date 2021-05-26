<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['password'] = bcrypt($request->password);

        $user = User::create($validatedData);
        $accessToken = $user->createToken(
            'accessToken',
            ['user', 'all', 'products']
        )->accessToken;

        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'message' => 'Registration successful'
        ]);
    }

    public function login(LoginUserRequest $request)
    {
        //validate users info and grant access
        $loginCredentials = $request->validated();
        if (!Auth::attempt($loginCredentials)) {
            return response(['message' => 'Invalid Credentials']);
        }

        $accessToken = Auth::user()->createToken(
            'accessToken',
            ['all', 'user', 'products']
        )->accessToken;
        return response()->json([
            'email' => $loginCredentials['email'],
            'access_token' => $accessToken,
            'message' => 'Login Successful'
        ]);
    }
}