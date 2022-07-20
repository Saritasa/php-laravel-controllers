# Changes History

4.0.0
-----
+ Add PageRequest, ChangePassword request
+ Depend on [saritasa/laravel-fluent-validation](https://github.com/Saritasa/php-laravel-fluent-validation) and [saritasa/dingo-api-custom](https://github.com/Saritasa/php-dingo-api-custom)
+ Remove ApiResourceRegistrar and WebResourceRegistrar (standard router is enough)

3.3.5
-----
Require release tymon/jwt-auth version instead of RC

3.3.4
-----
If token refresh failed return 403 code, not 401 - to prevent infinite refresh loop easier

3.3.3
-----
Fix issue with wrong method name `create` instead of `store` in ResourceApiController

3.3.2
-----
- Update the signature of the Saritasa\LaravelControllers\Api\ResetPasswordApiController::sendResetResponse method
to match the new signature of the Illuminate\Foundation\Auth\ResetsPasswords trait
- Update the response of the Saritasa\LaravelControllers\Api\ResetPasswordApiController::sendResetFailedResponse method
to display message instead of message token

3.3.1
-----
- Update the signature of the Saritasa\LaravelControllers\Api\ForgotPasswordApiController::sendResetLinkResponse method
to match the new signature of the Illuminate\Foundation\Auth\SendsPasswordResetEmails trait
- Set the minimal version of the Laravel to 5.7

3.3.0
-----
Compatibility with Laravel 6

3.2.0
-----
Fix show, update, delete methods in ResourceApiController - user repository to load models, rather than binding

3.1.1
-----
Fix logout error message

3.1.0
-----
- Update versions of dependent packages
- Added compatibility with laravel 5.8

3.0.5
-----
Fix issue when rotatable model has key different from `id` 

3.0.4
-----
Fix the paths of the resource files.

3.0.3
-----
Switched to Dingo/Api 2.0 beta (which contains bugfix in authentication)

3.0.2
-----
Fixed controller method/route name when defined resource with ApiResourceRegistrar from create to store.

3.0.1
-----
- Fixed issue with missing Arrayable contract at Responses DTO models.

3.0.0
-----
- Change namespace from Saritasa\Laravel\Controllers to Saritasa\LaravelControllers
- Add ability to bind models for controller on side of route creation
- Update behavior of default laravel router that now using Repository layer to get entities
from db when controller using model binding ability
- Update behavior of default laravel controller dispatcher that now not overrides early resolved
model bindings on
- Remove IApiResourceController, UserApiController, MacroServiceProvider, RevisionsServiceProvider,
AuthJWTService, BaseMarkupController, IWebResourceController
- Improve application structure
- Improve documentation
- Add more unit tests
- Switch minimum version of php to 7.1

2.0.8
-----
Add [laravelcollective/html](https://github.com/LaravelCollective/html) as dependency

2.0.7
-----
Explicitly add [dingo/api](https://github.com/dingo/api) as dependency

2.0.6
-----
Do not require minimum-stability of packages

2.0.5
-----
Enable Laravel's package discovery https://laravel.com/docs/5.5/packages#package-discovery

2.0.4
-----
Fix controllers inheritance (override validate method was incompatible)

2.0.3
-----
Trim slashes from resource controller route names in Api/Web route registrars

2.0.2
-----
- BaseApiController inherited from BaseController, this gets AuthorizesRequests trait
- BaseController does not uses DispatchesJobs trait

2.0.1
-----
Fix ResetPasswordController namespace

2.0.0
-----
Get rid of DTO postfix in DTO models: rename AddressDTO, AuthSuccessDTO

1.0.13
------
- Add SuccessMessage and ErrorMessage, use in controller responses
- Mark MessageDTO as deprecated

1.0.12
------
Add missing parent constructor call in ForgotPasswordApiController

1.0.11
------
Update documentation
Remove RequestResetPasswordApiController (duplicates ForgotPasswordApiController)

1.0.10
------
Add missing auth.failed language value
Add controllers:
- ForgotPasswordApiController
- RequestResetPasswordApiController
- ResetPasswordApiController

1.0.9
-----
Fix AuthJWTService error, causing authorization fail

1.0.8
-----
Fix loading translation messages from resources

1.0.7
-----
Update dependencies versions
Change AuthJWTService->auth() from credentials array to $email and $password params

1.0.6
-----
Rename AuthenticateApiController to JWTAuthApiController

1.0.5
-----
Fix DtoModel namespace

1.0.4
-----
Update AuthenticateApiController

1.0.3
-----
Add validation to BaseApiController
Remove clones of BaseApiController from related packages

1.0.2
-----
- Add TransformException

1.0.1
-----
- Fix namespace

1.0.0
-----

- Initial version:

IDataTransformer
BaseTransformer
LimitFieldsTransformer
CombineTransformer
