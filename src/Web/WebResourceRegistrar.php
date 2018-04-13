<?php

namespace Saritasa\Laravel\Controllers\Web;

use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Routing\Router;
use InvalidArgumentException;

/**
 * Wrapper for Illuminate router, adds concise methods for URLs registration.
 */
final class WebResourceRegistrar
{
    public const OPTION_ONLY = 'only';
    public const OPTION_EXPECT = 'expect';
    private const GET = 'get';
    private const POST = 'post';
    private const PUT = 'put';
    private const PATCH = 'patch';
    private const DELETE = 'delete';

    /**
     *  Original Laravel router service.
     *
     * @var Router
     */
    private $router;

    private $default = [
        'index' => ['verb' => self::GET, 'route' => ''],
        'indexData' => ['verb' => self::GET, 'route' => '', 'ajax' => true],
        'create' => ['verb' => self::GET, 'route' => '/create'],
        'store' => ['verb' => self::POST, 'route' => '', 'ajax' => true],
        'show' => ['verb' => self::GET, 'route' => '/{id}'],
        'read' => ['verb' => self::GET, 'route' => '/{id}', 'ajax' => true],
        'edit' => ['verb' => self::GET, 'route' => '/{id}/edit'],
        'update' => ['verb' => self::PUT, 'route' => '/{id}', 'ajax' => true],
        'destroy' => ['verb' => self::DELETE, 'route' => '/{id}', 'ajax' => true]
    ];

    public const VERBS = [self::GET, self::POST, self::PUT, self::PATCH, self::DELETE];

    /**
     * Wrapper for Illuminate router, adds concise methods for URLs registration.
     *
     * @param Registrar $router Original Laravel router service to wrap
     */
    public function __construct(Registrar $router)
    {
        $this->router = $router;
    }

    /**
     * Registers controller methods
     *
     * index -   as GET /resourceName - page, that displays list of resource entities
     * indexData as GET /resourceName (for AJAX requests only) - gets data for index page
     *           (usually paginated data in JSON, ex. for DataTables)
     * create -  as POST /resourceName - page, that displays new entity form
     * store -   as POST /resourceName (for AJAX requests only) method, that validates and creates new entity
     * show -    as GET /resourceName/{id} - page, that displays existing entity (usually in read-only mode)
     * read -    as GET /resourceName/{id} (for AJAX requests only) - gets JSON data for entity details display
     * edit -    as GET /resourceName/{id}/edit - page, that displays existing entity edit form
     * update -  as PUT /resourceName/{id} (for AJAX requests only) - method, that validates and saves edited changes
     * destroy - as DELETE /resourceName/{id} (for AJAX requests only) - delete entity
     *
     * @param string $resourceName URI of resource
     * @param string $controller FQDN Class name of Controller, which contains action method
     * @param array $options options, passed to router on route registration
     * @param string|null $modelClass Model class to resolve binding
     * @param string|null $modelName Model parameter name
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function resource(
        string $resourceName,
        string $controller,
        array $options = [],
        ?string $modelClass = null,
        ?string $modelName = null
    ): void {
        $controller = $this->removeNamespace($controller);
        $routes = [];
        if (count($options) === 0) {
            $routes = $this->default;
        } elseif (isset($options[static::OPTION_ONLY])) {
            $routes = array_intersect_key($this->default, $this->asMap($options[static::OPTION_ONLY]));
        } elseif (isset($options[static::OPTION_EXPECT])) {
            $routes = array_diff_key($this->default, $this->asMap($options[static::OPTION_EXPECT]));
        }

        foreach (static::VERBS as $verb) {
            if (isset($options[$verb])) {
                $actions = $this->asArray($options[$verb]);
                if (!is_array($actions)) {
                    $t = gettype($actions);
                    throw new InvalidArgumentException("{$options[$verb]} must contain string or array. $t was given");
                }

                foreach ($actions as $action) {
                    $routes[$action] = ['verb' => $verb, 'route' => "/$action"];
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
            $route = $resourceName . $opt['route'];
            $routeOptions = [
                'as' => trim($resourceName . '.' . $action, '/'),
                'uses' => $controller . '@' . $action,
                'mapping' => $mapping,
            ];
            if ($modelName) {
                $route = str_replace('{id}', "{{$modelName}}", $route);
            }
            if (isset($opt['ajax']) && $opt['ajax'] === true) {
                $routeOptions['prefix'] = 'ajax';
            }
            $this->router->$verb($route, $routeOptions);
        }
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
     * Actually called method, when user calls verb methods.
     *
     * @param string $verb - one of GET / POST / PUT / PATCH / DELETE
     * @param string $path - URL path
     * @param string $controller Class, containing action method
     * @param string|null $action method, which will be executed on route hit
     * @param string|null $route - route name
     * @param array $mapping Model bindings mapping
     *
     * @return mixed
     */
    private function action(
        string $verb,
        string $path,
        string $controller,
        string $action = null,
        string $route = null,
        array $mapping = []
    ) {
        $controller = $this->removeNamespace($controller);
        $pos = strrpos($path, '/', -1);
        $pathLastSegment = $pos ? substr($path, $pos + 1) : $path;

        if (!$action) {
            $action = $pathLastSegment;
        }
        if (!$route) {
            $route = strtolower(str_replace('/', '.', $path));
            // Small piece of magic: make auto-named routes look nicer
            if ($pathLastSegment != $action) {
                if (strrpos($route, '.' . $pathLastSegment, -1) === false) {
                    $route = "$route.$action";
                } else {
                    $route = str_replace('.' . $pathLastSegment, '.' . $action, $route);
                }
            }
            $route = trim(strtolower($route), '.');
        }
        return $this->router->$verb(
            $path,
            ['uses' => $controller . '@' . $action, 'as' => $route, 'mapping' => $mapping]
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
            foreach ($keys as $i => $key) {
                $keys[$i] = trim($key);
            }
            return $keys;
        }
        return null;
    }

    /**
     * Flip given parameter if it array.
     * Converts to array and then flip parameter if it string.
     *
     * @param string|array $value Parameter to flip
     * @return array|null
     */
    private function asMap($value): ?array
    {
        $arr = is_array($value) ? $value : $this->asArray($value);

        return $arr ? array_flip($arr) : null;
    }

    /**
     * Removes namespace from given class.
     *
     * @param string $controllerClass Class to remove namespace
     *
     * @return string
     */
    private function removeNamespace(string $controllerClass): string
    {
        $controllerClass = trim($controllerClass, '\t\n\0\\');

        $bsIndex = strrpos($controllerClass, '\\');
        if ($bsIndex !== false) {
            return substr($controllerClass, $bsIndex + 1);
        }

        return $controllerClass;
    }
}
