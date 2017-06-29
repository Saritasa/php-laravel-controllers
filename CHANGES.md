# Changes History

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
