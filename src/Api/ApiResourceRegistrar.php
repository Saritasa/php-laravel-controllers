<?php

namespace Saritasa\Laravel\Controllers\Api;

use Dingo\Api\Routing\Router;
use InvalidArgumentException;

/**
 * Wrapper for Dingo router, adds concise methods for API URLs registration.
 */
final class ApiResourceRegistrar
{
    private const GET = 'get';
    private const POST = 'post';
    private const PUT = 'put';
    private const PATCH = 'patch';
    private const DELETE = 'delete';

    /**
     * Original Dingo/API router service.
     *
     * @var Router
     */
    private $api;

    private $default = [
        'index' => ['verb' => self::GET, 'route' => ''],
        'create' => ['verb' => self::POST, 'route' => ''],
        'show' => ['verb' => self::GET, 'route' => '/{id}'],
        'update' => ['verb' => self::PUT, 'route' => '/{id}'],
        'destroy' => ['verb' => self::DELETE, 'route' => '/{id}']
    ];

    public const VERBS = [self::GET, self::POST, self::PUT, self::PATCH, self::DELETE];

    /**
     * Wrapper for Dingo router, adds concise methods for API URLs registration.
     *
     * @param Router $api Original Dingo/API router service to wrap
     */
    public function __construct(Router $api)
    {
        $this->api = $api;
    }

    /**
     * Registers controller methods
     *
     * index -   as GET /resourceName
     * create -  as POST /resourceName
     * show -    as GET /resourceName/{id}
     * update -  as PUT /resourceName/{id}
     * destroy - as DELETE /resourceName/{id}
     *
     * @param string $resourceName URI of resource
     * @param string $controller FQDN Class name of Controller, which contains action method
     * @param array $options options, passed to router on route registration
     * @param string $modelClass Model to resolve binding
     * @param string $modelName Model parameter name
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function resource(
        string $resourceName,
        string $controller,
        array $options = [],
        string $modelClass = null,
        string $modelName = null
    ): void {
        $routes = [];
        if (!$options || !count($options)) {
            $routes = $this->default;
        } elseif (isset($options['only'])) {
            $routes = array_intersect_key($this->default, $this->asArray($options['only']));
        } elseif (isset($options['except'])) {
            $routes = array_diff_key($this->default, $this->asArray($options['except']));
        }

        foreach (static::VERBS as $verb) {
            if (isset($options[$verb])) {
                $actions = $this->asArray($options[$verb]);
                if (!is_array($actions)) {
                    $t = gettype($actions);
                    throw new InvalidArgumentException("\$options['$verb'] must contain string or array. $t was given");
                }

                foreach ($actions as $action => $i) {
                    $routes[$action] = ['verb' => $verb, 'route' => '/' . $action];
                }
            }
        }

        $mapping = [];

        if ($modelClass) {
            $modelName = lcfirst($modelName ?? $this->getShortClassName($modelClass));
            $mapping[$modelName] = $modelClass;
        }

        foreach ($routes as $action => $opt) {
            $verb = $opt['verb'];
            $route = $opt['route'];
            if ($modelName) {
                $route = str_replace('{id}', "{{$modelName}}", $opt['route']);
            }

            $this->api->$verb($resourceName . $route, [
                'as' => trim($resourceName . '.' . $action),
                'uses' => $controller . '@' . $action,
                'mapping' => $mapping,
            ]);
        }
    }

    /**
     * Add get route.
     *
     * @param string $path URL path
     * @param string $controller Class, containing action method
     * @param string|null $action Method, which will be executed on route hit
     * @param string|null $route Route name
     * @param array $mapping Model bindings mapping
     *
     * @return void
     */
    public function get(
        string $path,
        string $controller,
        ?string $action = null,
        ?string $route = null,
        array $mapping = []
    ): void {
        $this->action(static::GET, $path, $controller, $action, $route, $mapping);
    }

    /**
     * Add post route.
     *
     * @param string $path URL path
     * @param string $controller Class, containing action method
     * @param string|null $action Method, which will be executed on route hit
     * @param string|null $route Route name
     * @param array $mapping Model bindings mapping
     *
     * @return void
     */
    public function post(
        string $path,
        string $controller,
        ?string $action = null,
        ?string $route = null,
        array $mapping = []
    ): void {
        $this->action(static::POST, $path, $controller, $action, $route, $mapping);
    }

    /**
     * Add patch route.
     *
     * @param string $path URL path
     * @param string $controller Class, containing action method
     * @param string|null $action Method, which will be executed on route hit
     * @param string|null $route Route name
     * @param array $mapping Model bindings mapping
     *
     * @return void
     */
    public function patch(
        string $path,
        string $controller,
        ?string $action = null,
        ?string $route = null,
        array $mapping = []
    ): void {
        $this->action(static::PATCH, $path, $controller, $action, $route, $mapping);
    }

    /**
     * Add put route.
     *
     * @param string $path URL path
     * @param string $controller Class, containing action method
     * @param string|null $action Method, which will be executed on route hit
     * @param string|null $route Route name
     * @param array $mapping Model bindings mapping
     *
     * @return void
     */
    public function put(
        string $path,
        string $controller,
        ?string $action = null,
        ?string $route = null,
        array $mapping = []
    ): void {
        $this->action(static::PUT, $path, $controller, $action, $route, $mapping);
    }

    /**
     * Add delete route.
     *
     * @param string $path URL path
     * @param string $controller Class, containing action method
     * @param string|null $action Method, which will be executed on route hit
     * @param string|null $route Route name
     * @param array $mapping Model bindings mapping
     *
     * @return void
     */
    public function delete(
        string $path,
        string $controller,
        ?string $action = null,
        ?string $route = null,
        array $mapping = []
    ): void {
        $this->action(static::DELETE, $path, $controller, $action, $route, $mapping);
    }

    /**
     * Resolve model class name. Ex: App\Models\User -> User.
     *
     * @param string $modelClass Class name to resolve.
     *
     * @return string
     * @throws \ReflectionException
     */
    protected function getShortClassName(string $modelClass): string
    {
        $reflectionClass = new \ReflectionClass($modelClass);
        return $reflectionClass->getShortName();
    }

    /**
     * Actually called method, when user calls verb methods
     *
     * @param string $verb - one of GET / POST / PUT / DELETE
     * @param string $path URL path
     * @param string $controller Class, containing action method
     * @param string|null $action Method, which will be executed on route hit
     * @param string|null $route Route name
     * @param array $mapping Model bindings mapping
     *
     * @return mixed
     */
    private function action(
        string $verb,
        string $path,
        string $controller,
        ?string $action = null,
        ?string $route = null,
        array $mapping = []
    ) {
        $pos = strrpos($path, '/', -1);
        $pathLastSegment = $pos ? substr($path, $pos + 1) : $path;

        if (!$action) {
            $action = $pathLastSegment;
        }
        if (!$route) {
            $route = strtolower(str_replace('/', '.', $path));
            // Small piece of magic: make auto-named routes look nicer
            if ($pathLastSegment !== $action) {
                if (strrpos($route, '.' . $pathLastSegment, -1) === false) {
                    $route = "$route.$action";
                } else {
                    $route = str_replace('.' . $pathLastSegment, '.' . $action, $route);
                }
            }
            $route = strtolower($route);
        }
        return $this->api->$verb(
            $path ?? $action,
            ['uses' => "$controller@$action", 'as' => $route, 'mapping' => $mapping]
        );
    }

    /**
     * Converts params to needed form.
     *
     * @param array|string $value Params ro converts
     *
     * @return array|null
     */
    private function asArray($value): ?array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $keys = explode(',', $value);
            return array_flip($keys);
        }
        return null;
    }
}
