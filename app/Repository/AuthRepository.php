<?php

namespace App\Repository;

use App\Http\Resources\User;
use App\Repository\Actors\UserActor;
use Illuminate\Support\Facades\Hash;
use App\Events\UserRegistered;

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
        //Create a record and send response to the controller
        $this->user->create(
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