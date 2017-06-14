# Laravel Controllers

Controllers for common UI and endpoints in Laravel,
like API authentication, password change, login page, etc.

## Laravel 5.x

Install the ```saritasa/laravel-controllers``` package:

```bash
$ composer require saritasa/laravel-controllers
```

Add the ControllersServiceProvider service provider in ``config/app.php``:

```php
'providers' => array(
    // ...
    Saritasa\Laravel\Controllers\ControllersServiceProvider::class,
)
```

## Available controllers

There are 2 types of controllers:
* **Web** - interactive UI for user - traditional Laravel controllers.
  Many of them just provide out-of-the-box Laravel functionality,
  using built-in traits.
* **Api** - for programmatic integration with 3d party applications,
  like mobile apps (iOS, Android) or single-page HTML applications,
  built on modern frontend frameworks - [React.JS](http://reactjs.com),
  [AngularJS](https://angularjs.org/), etc.
  API utilizes [Dingo/Api](https://github.com/dingo/api) library
  and custom extensions for it: [saritasa/dingo-api-custom](https://github.com/Saritasa/php-dingo-api-custom)

Controllers, described below, exist, but you
must register routes for them manually

### BaseApiController
Base API controller, utilizing helpers from Dingo/API package.
Recommended to use as base controller for other API controllers.

#### Methods

* function json($data, IDataTransformer $transformer = null): Response
* function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])

**Example**:
```php
class UserApiController extends BaseApiController
{
    public function __construct(UserTransformer $userTransformer) {
        parent::__construct($userTransformer);
    }

    public function editUserProfile(Request $request, User $user): Response {
        $this->validate($request, $user->getRuels());
        $user->fill($request->all());
        $user->save();
        return $this->json($user);
    }
}
```

### JWTAuthApiController
Authenticate API Controller. Uses JWT authentication
Utilizes [Dingo\Api JWT Auth](https://github.com/dingo/api/wiki/Authentication#json-web-tokens-jwt)
 settings and underlying [tymon\jwt-auth](https://github.com/tymondesigns/jwt-auth)

**Example**: routes\api.php:
```php
app('api.router')->version(config('api.version'), ['namespace' => 'Saritasa\Laravel\Controllers\Api'],
  function(\Dingo\Api\Routing\ $api) {
    // Authentication
    $api->post('auth', 'AuthController@login');                             // Login
    $api->put('auth', 'AuthController@refreshToken');                       // Refresh expired token
    $api->delete('auth', 'AuthController@logout')->middleware('api.auth');  // Logout
});
```

### ForgotPasswordApiController, ResetPasswordApiController
These controllers are responsible for handling password reset emails.
Utilize native Laravel password management without UI, in JSON API.

**Example**: routes\api.php:
```php
app('api.router')->version(config('api.version'), ['namespace' => 'Saritasa\Laravel\Controllers\Api'],
  function(\Dingo\Api\Routing\Router $api) {
    $api->post('auth/password/reset', 'ForgotPasswordApiController@sendResetLinkEmail');
    $api->put('auth/password/reset', 'ResetPasswordApiController@reset');
});
```


## Contributing

1. Create fork
2. Checkout fork
3. Develop locally as usual. **Code must follow [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/)**
4. Update *README.md* to describe new or changed functionality. Add changes description to *CHANGES.md* file.
5. When ready, create pull request

## Resources

* [Bug Tracker](http://github.com/saritasa/php-laravel-controllers/issues)
* [Code](http://github.com/saritasa/php-laravel-controllers)
* [Changes History](CHANGES.md)
* [Authors](http://github.com/saritasa/php-laravel-controllers/contributors)
