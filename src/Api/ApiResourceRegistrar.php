<?php

namespace Saritasa\Laravel\Controllers\Api;

use Saritasa\Exceptions\ConfigurationException;
use Dingo\Api\Routing\Router;

/**
 * Wrapper for Dingo router, adds concise methods for API URLs registration.
 * @method void get(string $resource, string $controller, string $action = null, string $route = null) Add POST route
 * @method void post(string $resource, string $controller, string $action = null, string $route = null) Add POST route
 * @method void patch(string $resource, string $controller, string $action = null, string $route = null) Add POST route
 * @method void put(string $resource, string $controller, string $action = null, string $route = null) Add POST route
 * @method void delete(string $resource, string $controller, string $action = null, string $route = null) Add POST route
 */
class ApiResourceRegistrar
{
    /**
     * @var Router
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
     * @param string $resourceName
     * @param string $controller
     * @param array $options
     */
    public function resource(string $resourceName, string $controller, array $options = [])
    {
        $routes = [];
        if (!$options || !count($options)) {
            $routes = $this->default;
        } else if (isset($options['only'])) {
            $routes = array_intersect_key($this->default, $this->asArray($options['only']));
        } else if (isset($options['except'])) {
            $routes = array_diff_key($this->default, $this->asArray($options['except']));
        }

        foreach (static::VERBS as $verb) {
            if (isset($options[$verb])) {
                $actions = $this->asArray($options[$verb]);
                if (!is_array($actions)) {
                    $t = gettype($actions);
                    throw new \InvalidArgumentException("\$options['$verb'] must contain string or array. $t was given");
                }

                foreach ($actions as $action => $i) {
                    $routes[$action] = ['verb' => $verb, 'route' => '/'.$action];
                }
            }
        }

        foreach ($routes as $action => $opt) {
            $verb = $opt['verb'];
            $this->api->$verb($resourceName.$opt['route'], [
                'as' => $resourceName.'.'.$action,
                'uses' => $controller.'@'.$action
            ]);
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

    private function asArray($value): array
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