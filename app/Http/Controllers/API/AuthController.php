<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repository\AuthRepository;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ValidatePhoneNumber;
use Illuminate\Support\Facades\App;

class AuthController extends Controller
{
    use ValidatePhoneNumber;
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
        $email = 'bail|required|email|unique:users';
        if (App::environment('production')) {
            $email = 'bail|required|email:rfc,dns|unique:users';
        }
        // Validate what's coming in
        $input = $request->all();
        Validator::make($input, [
            'name' => 'bail|required',
            'username' => 'bail|required',
            'phone_number' => 'bail|required|unique:users',
            'email' => $email,
            'password' => 'bail|required'
        ])->validate();

        //validate phone number
        $validatedPhoneNumber = $this->validatePhoneNumber($input['phone_number']);

        if (!$validatedPhoneNumber['valid']) {
            return response()->json([
                'error' => true,
                'msg' => [
                    'phone_number' => ["Not a valid phone number, Phone number should be in the format '08*********'"],
                ]
            ]);
        }

        $input['phone_number'] = $validatedPhoneNumber;

        //Register User and Return response
        $response = $this->auth->register($input);
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function confirmToken(Request $request): JsonResponse
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'email' => 'bail|required|email',
            'confirm_code' => 'bail|required'
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return response()->json(['error' => true, 'msg' => $messages]);
        }

        return response()->json($this->auth->confirmToken($input));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resendCode(Request $request): JsonResponse
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'email' => 'bail|required|email'
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return response()->json(['error' => true, 'msg' => $messages]);
        }

        return response()->json($this->auth->resendCode($input));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'email' => 'bail|required'
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return response()->json(['error' => true, 'msg' => $messages]);
        }

        return response()->json($this->auth->resetPassword($request->all()));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'oldpassword' => 'bail|required',
            'password' => 'bail|required|min:8',
            'confirmpassword' => 'bail|required|min:8'
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return response()->json(['error' => true, 'msg' => $messages]);
        }

        return response()->json($this->auth->changePassword($request));
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