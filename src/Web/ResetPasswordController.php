<?php

namespace Saritasa\LaravelControllers\Web;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;
use Saritasa\LaraveLControllers\BaseController;

/**
 * This controller is responsible for handling password reset requests.
 * https://laravel.com/docs/passwords
 */
class ResetPasswordController extends BaseController
{
    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Reset the given user's password.
     *
     * @param Model|Authenticatable $user User, who wants to reset password
     * @param string $password New Password
     *
     * @return void
     */
    protected function resetPassword($user, $password): void
    {
        $user->forceFill(['password' => $password, 'remember_token' => Str::random(60),])->save();

        $this->guard()->login($user);
    }
}
