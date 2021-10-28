<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repository\AuthRepository;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     *  @var AuthRepository
     */
    private $auth;

    /**
     * AuthController Constructor
     */

    public function __construct(AuthRepository $auth)
    {
        $this->auth = $auth;
    }
    /**
     *  @param Request $request
     * @return JsonResponse
     */

    public function register(Request $request): JsonResponse
    {
        // Validate what's coming in
        $validatedData = Validator::make($request->all(), [
            'name' => 'bail|required',
            'email' => 'bail|required|email|unique:users',
            'password' => 'bail|required'
        ])->validate();

        //Register User and Return response
        $response = $this->auth->register($validatedData);
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function multiRegister(Request $request): JsonResponse
    {
        // Validate what's coming in
        $validatedData = Validator::make($request->all(), [
            'users.*.name' => 'bail|required',
            'users.*.email' => 'bail|required|email|unique:users',
            'users.*.password' => 'bail|required'
        ])->validate();

        //Register User and Return response
        $response = $this->auth->multiRegister($validatedData);
        return response()->json($response, 201);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        //validate users info
        $validatedData = Validator::make(
            $request->all(),
            [
                'email' => 'bail|required|email',
                'password' => 'bail|required'
            ]
        )->validate();


        $response = $this->auth->login($validatedData);
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