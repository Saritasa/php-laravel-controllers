# Changes History

2.1.0
-----
Add model binding in ApiResourceRegistrar::resource
Add ApiResourceRegistrar test

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
