<?php

namespace App\Repository;

use App\Http\Resources\User;
use App\Repository\Actors\UserActor;
use Illuminate\Support\Facades\Hash;
use App\Events\UserRegistered;
use App\Events\UserActivated;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AuthRepository
{
    /**
     * @var UserActor
     * */
    private $user;

    /** AuthRepository constructor
     * @param UserActor $user
     */
    public function __construct(UserActor $user)
    {
        $this->user = $user;
    }
    /**
     * Register new user
     * @param $data
     * @return array []
     */
    public function register($data): array
    {
        $confirmation_code = Str::random(20);
        $confirm = rand(111111, 999999);

        //check if email exist
        $emailExists = $this->user->where('email', $data['email'])->first();
        if ($emailExists) {
            return [
                'error' => true,
                'msg' => [
                    'email' => 'Email associated with another account'
                ]
            ];
        }

        //check if phone number exist
        $phoneNumberExists = $this->user->where('phone_number', $data['phone_number'])->first();
        if ($phoneNumberExists) {
            return [
                'error' => true,
                'msg' => [
                    'email' => 'Phone Number associated with another account'
                ]
            ];
        }

        //check if username exist
        $userNameExists = $this->user->getModel()->where('username', 'LIKE', $data['phone_number'])->first();
        if ($userNameExists) {
            return [
                'error' => true,
                'msg' => [
                    'email' => 'Username associated with another account'
                ]
            ];
        }

        //Create a record and send response to the controller
        $this->user->create(
            [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
                'password' => bcrypt($data['password']),
                'remember_token' => $confirmation_code,
                'confirm_code' => $confirm,
                'active' => true,
                'created_at' => Carbon::now(),
                'scopes' => ['user', 'all', 'products']
            ]
        );

        $url = config('app.page_url') . '/confirm-account?email=' . $data['email'] . '&token=' . $confirmation_code;
        $this->user->findBy('username', $data['username']);

        $email_data = [
            'username' => $data['name'],
            'mailTo' => $data['email'],
            'url' => $url,
            'subject' => 'Verify Your Email To Complete Your Registration',
            'mail_body' => 'You\'re getting this mail because you successfully registered on our platform',
            'button_name' => 'Click to Confirm',
            // 'button_link' => 'http://localhost/api/login'
            // 'confirm_code' => $confirm
        ];

        event(new UserRegistered($email_data));

        return [
            'error' => false,
            'msg' => 'Registration Successful. Check your inbox for confirmation'
        ];
    }

    /** Register multiple users
     * @param $data
     * @return array []
     */

    public function multiRegister($data): array
    {
        //Create a record and send response to the controller
        foreach ($data['users'] as $key => $aUserData) {
            $this->user->create(
                [
                    'name' => $aUserData['name'],
                    'email' => $aUserData['email'],
                    'password' => bcrypt($aUserData['password']),
                    'scopes' => ['user', 'all', 'products']
                ]
            );
        }

        $email_data = [
            'username' => $aUserData['name'],
            'mailTo' => $aUserData['email'],
            'subject' => 'Successful Registration',
            'mail_body' => 'You\'re getting this mail because you successfully registered on our platform',
            'button_name' => 'Click to Login',
            'button_link' => 'http://localhost/api/login'
        ];

        event(new UserRegistered($email_data));

        return [
            'error' => false,
            'msg' => 'Registration Successful. Check your inbox for confirmation',
        ];
    }

    /**
     * Confirm user registration using email and token
     * @param $input
     * @return array []
     */
    public function confirmToken($input): array
    {
        $email = $input['email'];
        $confirm_code = $input['confirm_code'];

        $user = $this->user->findBy('email', $email);

        if (!empty($user)) {
            $since = Carbon::parse($user->activation_created)->diffInHours(Carbon::now());
            if ($since <= 24) {
                if (!$user->active) {
                    if ($user->remember_token === $confirm_code) {
                        $user->active = '1';
                        $user->remember_token = '';
                        $user->confirm_code = '';
                        $user->email_verified_at = Carbon::now();

                        $user->save();

                        event(new UserActivated($user, [
                            'subject' => 'Account Activated',
                            'name' => $user->name
                        ]));

                        return [
                            'msg' => 'Account has been activated successfully.',
                            'error' => false
                        ];
                    }
                    return [
                        'msg' => 'Invalid activation code',
                        'error' => true
                    ];
                }
                return [
                    'msg' => 'Account has already been verified.',
                    'error' => true
                ];
            }
            return [
                'msg' => 'Activation link has expired.',
                'error' => true
            ];
        }
        return [
            'msg' => 'Account does not exist',
            'error' => true
        ];
    }

    /**
     * Login User
     * @param $data
     * @return array []
     */

    public function login($data): array
    {
        $user_email = $data['email'];
        $user_password = $data['password'];

        //Get User's Record
        $user = $this->user->where('email', $user_email)->first();

        // Compare Db with Request Data
        if (!$user) {
            return [
                'error' => true,
                'msg' => 'User not found',
            ];
        }

        if (!Hash::check($user_password, $user->password)) {
            return [
                'error' => true,
                'mgs' => 'Invalid Credential',
            ];
        }

        //Create an access token for the user
        $accessToken = $user->createToken('accessToken', $user->scopes)->accessToken;
        User::withoutWrapping();
        return [
            'error' => false,
            'msg' => 'Login Successful',
            'data' => new User($user),
            'access_token' => $accessToken
        ];
    }
}