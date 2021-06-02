<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Repository\AuthRepository;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthController extends Controller
{
    private $authrepository;

    public function __construct(AuthRepository $authrepository) //constructor for the repository
    {
        $this->authrepository = $authrepository;
    }

    public function register()
    {
        //Validate what's coming in
        $validatedData = Validator::make(request()->all(), [
            'name' => 'bail|required',
            'email' => 'bail|required|email|unique:users',
            'password' => 'bail|required'
        ]);
        //Display validation error
        $this->validationError($validatedData);

        return $this->authrepository->register();
    }

    public function login()
    {
        //validate users info
        $validatedData = Validator::make(
            request()->all(),
            [
                'email' => 'bail|required|email',
                'password' => 'bail|required'
            ]
        );
        //Display validation error
        $this->validationError($validatedData);

        return $this->authrepository->login();
    }

    public function validationError($validatedData)
    {
        if ($validatedData->fails()) {
            $errors = $validatedData->errors();

            $response = response()->json([
                'message' => $errors->messages(), 'Validation Error'
            ]);

            throw new HttpResponseException($response);
        }
    }
}