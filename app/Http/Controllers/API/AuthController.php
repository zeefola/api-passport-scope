<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repository\AuthRepository;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $authrepository;

    public function __construct(AuthRepository $authrepository) //constructor for the repository
    {
        $this->authrepository = $authrepository;
    }

    public function register(Request $request)
    {
        // Validate what's coming in
        $validatedData = Validator::make($request->all(), [
            'name' => 'bail|required',
            'email' => 'bail|required|email|unique:users',
            'password' => 'bail|required'
        ])->validate();

        //Register User and Return response
        $response = $this->authrepository->register($validatedData);
        return response()->json($response);
    }

    public function multiRegister(Request $request)
    {
        // Validate what's coming in
        $validatedData = Validator::make($request->all(), [
            'users.*.name' => 'bail|required',
            'users.*.email' => 'bail|required|email|unique:users',
            'users.*.password' => 'bail|required'
        ])->validate();

        //Register User and Return response
        $response = $this->authrepository->multiRegister($validatedData);
        return response()->json($response, 201);
    }

    public function login(Request $request)
    {
        //validate users info
        $validatedData = Validator::make(
            $request->all(),
            [
                'email' => 'bail|required|email',
                'password' => 'bail|required'
            ]
        )->validate();


        $response = $this->authrepository->login($validatedData);

        return response()->json($response);
    }

    public function logout()
    {
        $user = Auth::user()->token();
        $user->revoke();
        return response()->json([
            'message' => 'Logged Out Successfully'
        ]);
    }


    // public function validationError($validatedData)
    // {
    //     if ($validatedData->fails()) {
    //         $errors = $validatedData->errors();

    //         $response = response()->json([
    //             'message' => $errors->messages(), 'Validation Error'
    //         ]);

    //         throw new HttpResponseException($response);
    //     }
    // }
}