<?php

namespace App\Repository;

use App\Http\Resources\User;
use App\Repository\Actors\UserActor;
use Illuminate\Support\Facades\Hash;
use App\Events\UserRegistered;
use App\Events\UserActivated;
use App\Events\UserChangePassword;
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

    /** Delete multiple users
     * @param $data
     * @return array []
     */

    public function deleteMultipleUser($data): array
    {
        // $ids = explode(',', $data);

        foreach ($data as $key => $id) {
            $user = $this->user->find($id);
            if (!$user) {
                return [
                    'error' => true,
                    'msg' => 'User with ' . $id . 'not found'
                ];
            }

            $this->user->where('id', $id)->delete();
        }

        return [
            'error' => false,
            'msg' => 'Users Deleted Successfully',
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
     * Resend user confirmation code for confirming registration
     * @param $input
     * @return array []
     */
    public function resendCode($input): array
    {
        $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
        $user = $this->user->findBy('email', $email);

        if (!empty($user)) {
            $confirmation_code = str_random(20);
            $confirm = rand(111111, 999999);

            $user->remember_token = $confirmation_code;
            $user->confirm_code = $confirm;
            $user->activation_created = Carbon::now();
            $user->save();

            $url = config('app.frontend_url') . '/confirm-account?email=' . $email . '&token=' . $confirmation_code;

            event(new UserRegistered($user, [
                'title' => 'Confirm Account',
                'name' => $user->name,
                'url' => $url,
                'email' => $email,
                'confirm_code' => $confirm
            ]));

            return [
                'msg' => 'Account activation code has been sent.',
                'error' => false
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
        $user_email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $user_password = $data['password'];

        //Get User's Record
        $user = $this->user->where('email', $user_email)->first();

        // Check if user account exists
        if (!$user) {
            return [
                'error' => true,
                'msg' => 'User not found',
            ];
        }

        // Check if user account has been activated
        if ($user->active == 0 && $user->remember_token) {
            return [
                'error' => true,
                'msg' => 'Account has not been activated.'
            ];
        }

        //validate password
        if (!Hash::check($user_password, $user->password)) {
            return [
                'error' => true,
                'mgs' => 'Invalid Credential',
            ];
        }

        //Create an access token for the user
        $accessToken = $user->createToken('accessToken', $user->scopes)->accessToken;
        $user->last_login = Carbon::now();
        $user->save();

        User::withoutWrapping();
        return [
            'error' => false,
            'msg' => 'Login Successful',
            'data' => new User($user),
            'access_token' => $accessToken
        ];
    }

    /**
     * Reset User Password
     * @param $input
     * @return array []
     */
    public function resetPassword($input): array
    {
        $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
        $user = $this->user->findBy('email', $email);

        if (!$user) {
            return [
                'msg' => 'Email is not associated with any account.',
                'error' => true
            ];
        }
        $password = str_random(6);
        $user->password = Hash::make($password);
        $user->save();

        event(new UserChangePassword($user, [
            'title' => 'Password Reset',
            'name' => $user->name,
            'password' => $password
        ]));

        return [
            'msg' => 'A new password has been sent to your email',
            'error' => false
        ];
    }

    /**
     * Change user password
     * @param $request
     * @return array []
     */
    public function changePassword($request): array
    {
        $input = $request->all();

        $old_password = $input['oldpassword'];
        $password = $input['password'];
        $confirm_password = $input['confirmpassword'];

        if ($password !== $confirm_password) {
            return [
                'error' => true,
                'msg' => 'Incorrect confirm password'
            ];
        }

        $user_id = $request->user()->id; //Auth::id();
        $user = $this->user->where('id', $user_id)->first();

        if (Hash::check($old_password, $user->password)) {
            $user->password = Hash::make($password);
            $user->save();

            return [
                'error' => false,
                'msg' => 'Password changed successfully'
            ];
        }
        return [
            'error' => true,
            'msg' => 'Old password is incorrect'
        ];
    }
}