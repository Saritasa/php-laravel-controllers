# Laravel Controllers    
[![PHP Unit](https://github.com/Saritasa/php-laravel-controllers/workflows/PHP%20Unit/badge.svg)](https://github.com/Saritasa/php-laravel-controllers/actions)
[![PHP CodeSniffer](https://github.com/Saritasa/php-laravel-controllers/workflows/PHP%20Codesniffer/badge.svg)](https://github.com/Saritasa/php-laravel-controllers/actions) 
[![codecov](https://codecov.io/gh/Saritasa/php-laravel-controllers/branch/master/graph/badge.svg)](https://codecov.io/gh/Saritasa/php-laravel-controllers) 
[![Release](https://img.shields.io/github/release/saritasa/php-laravel-controllers.svg)](https://github.com/Saritasa/php-laravel-controllers/releases) 
[![PHPv](https://img.shields.io/packagist/php-v/saritasa/laravel-controllers.svg)](http://www.php.net) 
[![Downloads](https://img.shields.io/packagist/dt/saritasa/laravel-controllers.svg)](https://packagist.org/packages/saritasa/laravel-controllers)    
    
Controllers for common UI and endpoints in Laravel,    
like API authentication, password change, login page, etc.    
    
## Laravel 5.7+/6.0  
Install the ```saritasa/laravel-controllers``` package:    
    
```bash
$ composer require saritasa/laravel-controllers  
```  
 ## Available controllers    
There are 2 types of controllers:    
* **Web** - interactive UI for user - traditional Laravel controllers.    
  Many of them just provide out-of-the-box Laravel functionality,    
  using built-in traits.    
* **Api** - for programmatic integration with 3d party applications,    
  like mobile apps (iOS, Android) or single-page HTML applications,    
  built on modern frontend frameworks - [React.JS](http://reactjs.com),   [AngularJS](https://angularjs.org/), [VueJs](https://vuejs.org/), etc.  
  API utilizes [Dingo/Api](https://github.com/dingo/api) library    
  and custom extensions for it: [saritasa/dingo-api-custom](https://github.com/Saritasa/php-dingo-api-custom)    
    
Controllers, described below, exist, but you  must register routes for them manually    
    
### BaseApiController Base API controller, utilizing helpers from Dingo/API package.    
Recommended to use as base controller for other API controllers.    
    
#### Methods    
* function json($data, IDataTransformer $transformer = null): Response    
* function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])    
    
**Example**:    
```php
class UserApiController extends BaseApiController
{    
    public function __construct(UserTransformer $userTransformer)  
	 {      
		 parent::__construct($userTransformer);  
	 }
	 public function editUserProfile(Request $request, User $user): Response  
	 {
		 $this->validate($request, $user->getRuels());
		 $user->fill($request->all());
		 $user->save();
		 return $this->json($user);
	  }
}
```    
 ### JWTAuthApiController Authenticate API Controller. Uses JWT authentication    
Utilizes [Dingo\Api JWT Auth](https://github.com/dingo/api/wiki/Authentication#json-web-tokens-jwt)    
settings and underlying [tymon\jwt-auth](https://github.com/tymondesigns/jwt-auth)    
    
**Example**: routes\api.php:    
```php
app('api.router')->version(config('api.version'), ['namespace' => 'Saritasa\Laravel\Controllers\Api'],    
 function (\Dingo\Api\Routing\Router $api) {  
 // Authentication $api->post('auth', 'AuthController@login');   // Login $api->put('auth', 'AuthController@refreshToken'); // Refresh expired token                
 $api->delete('auth', 'AuthController@logout')->middleware('api.auth'); // Logout  
 });
```  
### ResourceApiController  
Controller for typical CRUD operations when you don't need many additional logic.  
For using this functionality you can just create controller and set needed model class in property $modelClass  
**Example**:  
```php  
class CustomApiController extends ResourceApiController
{    
    /**
    * Define model class that is managed by this controller.
    *
    * @var string
    */
    protected $modelClass = App\Models\User::class;  
 }
 ```  
### ForgotPasswordApiController, ResetPasswordApiController These controllers are responsible for handling password reset emails.    
Utilize native Laravel password management without UI, in JSON API.    
    
**Example**: routes\api.php:    
```php
app('api.router')->version(config('api.version'), ['namespace' => 'Saritasa\Laravel\Controllers\Api'],    
function (\Dingo\Api\Routing\Router $api) { $api->post('auth/password/reset', 'ForgotPasswordApiController@sendResetLinkEmail'); $api->put('auth/password/reset', 'ResetPasswordApiController@reset');});
```    
 ## Routes registration examples:    
 ### Simple route
 ```php
 $registrar->get('users', ApiController::class, 'list'); 
```    
In this case ApiController::list will be calling with default dependency injection.    
    
### Route with parameters
```php    
 // Controller example
 class ApiController {    
 	public function show(int $id) {}
 }    
 // Route example
 $registrar->get('users/{id}', ApiController::class, 'show');
```
In this case ApiController::show will receive directly parameter from url. Ex: /user/5    
    
### Route with binding on controller side
```php
// Controller example
class ApiController {    
 	public function show(User $user) {}
}    
 // Route example
 $registrar->get('users/{user}', ApiController::class, 'show');
```
In this case ApiController::show try to find Model user by id and if not exists throws ModelNotFoundException.    
    
### Route with binding on route side
```php
// Controller example
class ApiController {    
 	public function show(Model $user) {}
}    
 // Route example
 $registrar->get('users/{user}', ApiController::class, 'show', null, ['user' => User::class]);
```
In this case no matter what type hint in controller, container will be trying to give object of class that you pass in router registrar.
So if method has type hinting of class which not a parent for given in router, TypeError will be thrown.    
    
#### Note: In 2 last cases classes which uses for model bindings must implement Illuminate\Contracts\Routing\UrlRoutable otherwise this parameters    
will be ignored.    
    
## Contributing    
1. Create fork, checkout it    
2. Develop locally as usual. **Code must follow [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/)** -    
    run [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) to ensure, that code follows style guides    
3. **Cover added functionality with unit tests** and run [PHPUnit](https://phpunit.de/) to make sure, that all tests pass    
4. Update [README.md](README.md) to describe new or changed functionality    
5. Add changes description to [CHANGES.md](CHANGES.md) file. Use [Semantic Versioning](https://semver.org/) convention to determine next version number.    
6. When ready, create pull request    
    
### Make shortcuts    
If you have [GNU Make](https://www.gnu.org/software/make/) installed, you can use following shortcuts:    
    
* ```make cs``` (instead of ```php vendor/bin/phpcs```) -    
    run static code analysis with [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)    
    to check code style    
* ```make csfix``` (instead of ```php vendor/bin/phpcbf```) -    
    fix code style violations with [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)    
    automatically, where possible (ex. PSR-2 code formatting violations)    
* ```make test``` (instead of ```php vendor/bin/phpunit```) -    
    run tests with [PHPUnit](https://phpunit.de/)    
* ```make install``` - instead of ```composer install``` * ```make all``` or just ```make``` without parameters -    
    invokes described above **install**, **cs**, **test** tasks sequentially -    
    project will be assembled, checked with linter and tested with one single command    
    
## Resources    
* [Bug Tracker](http://github.com/saritasa/php-laravel-controllers/issues)    
* [Code](http://github.com/saritasa/php-laravel-controllers)    
* [Changes History](CHANGES.md)    
* [Authors](http://github.com/saritasa/php-laravel-controllers/contributors)
