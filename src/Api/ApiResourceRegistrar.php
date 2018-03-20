<?php

namespace Saritasa\Laravel\Controllers\Api;

use InvalidArgumentException;
use Saritasa\Exceptions\ConfigurationException;
use Dingo\Api\Routing\Router;

/**
 * Wrapper for Dingo router, adds concise methods for API URLs registration.
 *
 * @method void get(string $resource, string $controller, string $action = null, string $route = null) Add POST route
 * @method void post(string $resource, string $controller, string $action = null, string $route = null) Add POST route
 * @method void patch(string $resource, string $controller, string $action = null, string $route = null) Add POST route
 * @method void put(string $resource, string $controller, string $action = null, string $route = null) Add POST route
 * @method void delete(string $resource, string $controller, string $action = null, string $route = null) Add POST route
 */
class ApiResourceRegistrar
{
    /**
     * @var Router Original Dingo/API router service
     */
    private $api;

    private $default = [
        'index' => ['verb' => 'get', 'route' => ''],
        'create' => ['verb' => 'post', 'route' => ''],
        'show' => ['verb' => 'get', 'route' => '/{id}'],
        'update' => ['verb' => 'put', 'route' => '/{id}'],
        'destroy' => ['verb' => 'delete', 'route' => '/{id}']
    ];
    
    const VERBS = ['get', 'post', 'put', 'patch', 'delete'];

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
     * @param string $modelClass Model class short name to bind in controller method instead id.
     */
    public function resource(string $resourceName, string $controller, array $options = [], string $modelClass = null)
    {
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
                    $routes[$action] = ['verb' => $verb, 'route' => '/'.$action];
                }
            }
        }

        if ($modelClass) {
            $modelClass = $this->resolveModelClass($modelClass);
        }

        foreach ($routes as $action => $opt) {
            $verb = $opt['verb'];
            $route = $opt['route'];
            if ($modelClass) {
                $route = str_replace('{id}', "{{$modelClass}}", $opt['route']);
            }
            $this->api->$verb($resourceName.$route, [
                'as' => trim($resourceName.'.'.$action),
                'uses' => $controller.'@'.$action
            ]);
        }
    }

    /**
     * Resolves model class name. Ex: App\Models\User -> User.
     * If class not existing return given parameter.
     *
     * @param string $modelClass Class name to resolve.
     *
     * @return string
     */
    protected function resolveModelClass(string $modelClass): string
    {
        try {
            $reflectionClass = new \ReflectionClass($modelClass);
            return $reflectionClass->getShortName();
        } catch (\ReflectionException $exception) {
            return ucfirst($modelClass);
        }
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, static::VERBS)) {
            array_splice($arguments, 0, 0, [$name]);
            return call_user_func_array([$this, 'action'], $arguments);
        }
        throw new ConfigurationException("Unknown HTTP verb $name used for route $arguments[0]");
    }

    /**
     * Actually called method, when user calls verb methods
     *
     * @param string $verb - one of GET / POST / PUT / DELETE
     * @param string $path - URL path
     * @param string $controller Class, containing action method
     * @param string|null $action method, which will be executed on route hit
     * @param string|null $route - route name
     * @return mixed
     */
    private function action(string $verb, string $path, string $controller, string $action = null, string $route = null)
    {
        $pos = strrpos($path, '/', -1);
        $pathLastSegment = $pos ? substr($path, $pos+1) : $path;

        if (!$action) {
            $action = $pathLastSegment;
        }
        if (!$route) {
            $route = strtolower(str_replace('/', '.', $path));
            // Small piece of magic: make auto-named routes look nicer
            if ($pathLastSegment != $action) {
                if (strrpos($route, '.'.$pathLastSegment, -1) === false) {
                    $route = "$route.$action";
                } else {
                    $route = str_replace('.' . $pathLastSegment, '.' . $action, $route);
                }
            }
            $route = strtolower($route);
        }
        return $this->api->$verb($path ?: $action, ['uses' => $controller.'@'.$action, 'as' => $route]);
    }

    /**
     * Converts params to needed form.
     *
     * @param array|string $value Params ro converts
     *
     * @return array|null
     */
    private function asArray($value)
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
