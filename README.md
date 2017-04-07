# Laravel Controllers

Controllers for common UI and endpoints in Laravel,
like API authentication, notification message list, notification settings, etc.

## Laravel 5.x

Install the ```saritasa/laravel-controllers``` package:

```bash
$ composer require saritasa/php-transformers
```

Add the ControllersServiceProvider service provider in ``config/app.php``:

```php
'providers' => array(
    // ...
    Saritasa\Laravel\Controllers\ControllersServiceProvider::class,
)
```

## Available transformers

### IDataTransformer
Interface to unlink dependency from League/Fractal library.
Ensure, that every transformer implementation in this library has this interface.

**Example**:
```
class AnotherTransformerWrapper implements IDataTransformer
{
    public function __construct(IDataTransformer $nestedTransformer) {}
}
```

### CombineTransformer
Apply multiple transformers in order of arguments;

**Example**:
```
class UserProfileTransformer extends CombineTransformer
{
    public function __construct()
    {
        parent::__construct(
            new PreloadUserAvatarTransformer(),
            new PreloadUserSettingsTransformer()
        );
    }
}

```

### LimitFieldsTransformer
Result will contain only selected fields from source object.

**Example**:
```php
$publicUserProfileTransformer = new LimitFieldsTransformer('id', 'name', 'created_at');

```

## Exceptions
### TransformException
Should be thrown by class, implementing IDataTransformer, if it encounters data,
that cannot be transformed.

**Example**:
```php
function transform(Arrayable $data) {
    if (!$data->author) {
        new TransformException($this, "Author may not be empty");
    }
    // ...
}
```

## Contributing

1. Create fork
2. Checkout fork
3. Develop locally as usual. **Code must follow [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/)**
4. Update README.md to describe new or changed functionality. Add changes description to CHANGE file.
5. When ready, create pull request

## Resources

* [Bug Tracker](http://github.com/saritasa/php-transformers/issues)
* [Code](http://github.com/saritasa/php-transformers)
