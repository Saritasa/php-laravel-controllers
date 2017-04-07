<?php

namespace Saritasa\Laravel\Controllers\Web;

use Saritasa\Exceptions\ConfigurationException;
use Illuminate\Routing\Router;

/**
 * Wrapper for Illuminate router, adds concise methods for URLs registration.
 *
 * @method void get(string $resource, string $controller, string $action = null, string $route = null) Add GET route
 * @method void post(string $resource, string $controller, string $action = null, string $route = null) Add POST route
 * @method void patch(string $resource, string $controller, string $action = null, string $route = null) Add PATCH route
 * @method void put(string $resource, string $controller, string $action = null, string $route = null) Add PUT route
 * @method void delete(string $resource, string $controller, string $action = null, string $route = null) Add DELETE route
 */
class WebResourceRegistrar
{
    /**
     * @var Router
     */
    private $router;

    private $default = [
        'index' => ['verb' => 'get', 'route' => ''],
        'indexData' => ['verb' => 'get', 'route' => '', 'ajax' => true],
        'create' => ['verb' => 'get', 'route' => '/create'],
        'store' => ['verb' => 'post', 'route' => '', 'ajax' => true],
        'show' => ['verb' => 'get', 'route' => '/{id}'],
        'read' => ['verb' => 'get', 'route' => '/{id}', 'ajax' => true],
        'edit' => ['verb' => 'get', 'route' => '/{id}/edit'],
        'update' => ['verb' => 'put', 'route' => '/{id}', 'ajax' => true],
        'destroy' => ['verb' => 'delete', 'route' => '/{id}', 'ajax' => true]
    ];

    const VERBS = ['get', 'post', 'put', 'patch', 'delete'];

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Registers controller methods
     *
     * index -   as GET /resourceName - page, that displays list of resource entities
     * indexData as GET /resourceName (for AJAX requests only) - gets data for index page (usually paginated data in JSON, ex. for DataTables)
     * create -  as POST /resourceName - page, that displays new entity form
     * store -   as POST /resourceName (for AJAX requests only) method, that validates and creates new entity
     * show -    as GET /resourceName/{id} - page, that displays existing entity (usually in read-only mode)
     * read -    as GET /resourceName/{id} (for AJAX requests only) - gets JSON data for entity details display
     * edit -    as GET /resourceName/{id}/edit - page, that displays existing entity edit form
     * update -  as PUT /resourceName/{id} (for AJAX requests only) - method, that validates and saves edited changes
     * destroy - as DELETE /resourceName/{id} (for AJAX requests only) - delete entity
     *
     * @param string $resourceName
     * @param string $controller
     * @param array $options
     */
    public function resource(string $resourceName, string $controller, array $options = [])
    {
        $controller = $this->removeNamespace($controller);
        $routes = [];
        if (!$options || !count($options)) {
            $routes = $this->default;
        } else if (isset($options['only'])) {
            $routes = array_intersect_key($this->default, $this->asMap($options['only']));
        } else if (isset($options['except'])) {
            $routes = array_diff_key($this->default, $this->asMap($options['except']));
        }

        foreach (static::VERBS as $verb) {
            if (isset($options[$verb])) {
                $actions = $this->asArray($options[$verb]);
                if (!is_array($actions)) {
                    $t = gettype($actions);
                    throw new \InvalidArgumentException("\$options['$verb'] must contain string or array. $t was given");
                }

                foreach ($actions as $action) {
                    $routes[$action] = ['verb' => $verb, 'route' => '/'.$action];
                }
            }
        }

        foreach ($routes as $action => $opt) {
            $verb = $opt['verb'];
            $route = $resourceName.$opt['route'];
            $routeOptions = [
                'as' => $resourceName.'.'.$action,
                'uses' => $controller.'@'.$action
            ];
            if (isset($opt['ajax']) && $opt['ajax'] === true) {
                $routeOptions['prefix'] = 'ajax';
            }
            $this->router->$verb($route, $routeOptions);
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
     * @param string $verb - one of GET / POST / PUT / PATCH / DELETE
     * @param string $path - URL path
     * @param string $controller Class, containing action method
     * @param string|null $action method, which will be executed on route hit
     * @param string|null $route - route name
     * @return mixed
     */
    private function action(string $verb, string $path, string $controller, string $action = null, string $route = null)
    {
        $controller = $this->removeNamespace($controller);
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
            $route = trim(strtolower($route), '.');
        }
        return $this->router->$verb($path, ['uses' => $controller.'@'.$action, 'as' => $route]);
    }

    private function asArray($value): array
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

    private function asMap($value): array
    {
        $arr = is_array($value) ? $value : $this->asArray($value);
        return $arr == null ? null: array_flip($arr);
    }

    private function removeNamespace(string $controllerClass)
    {
        $controllerClass = trim($controllerClass, '\t\n\0\\');
        $bsIndex = strrpos($controllerClass, '\\') ;
        if ($bsIndex !== false) {
            return substr($controllerClass, $bsIndex+1);
        }
        return $controllerClass;
    }
}