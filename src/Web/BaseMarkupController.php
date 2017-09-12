<?php

namespace Saritasa\Laravel\Controllers\Web;

use Illuminate\Auth\Access\HandlesAuthorization;
use Saritasa\Laravel\Controllers\BaseController;

/**
 * This class used for markup only.
 */
class BaseMarkupController extends BaseController
{
    protected $baseRoute = 'markup';

    use HandlesAuthorization;

    public function __construct()
    {
        if (!static::isEnabled()) {
            $this->deny("This feature can be available during development only");
        }
    }

    // List of markup pages
    public function index()
    {
        $result = "<h1>Markup Pages</h1><ul>";

        foreach (static::getMarkupMethods() as $method) {
            $result .= "<li><a href='".route("$this->baseRoute.$method")."'>$method</a><br/>";
        }

        return $result."</ul>";
    }

    // Get list of markup methods via reflection
    public static function getMarkupMethods(): array
    {
        $reflect = new \ReflectionClass(static::class);
        $except = ['index', '__construct', 'allow', 'deny'];

        $result = [];
        foreach ($reflect->getMethods() as $method) {
            if ($method->isStatic() ||
                $method->isPrivate() ||
                !is_subclass_of($method->class, self::class) ||
                in_array($method->name, $except) ) {
                continue;
            }

            $result[] = $method->name;
        }

        return $result;
    }

    /**
     * Markup controller is enabled, when app in debug mode or this is a development environment
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return config('app.debug') || config('app.env') == 'development';
    }
}
