<?php

namespace Saritasa\LaravelControllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

/**
 * Custom base controller for Saritasa application.
 * Authorizes requests and validates requests.
 */
class BaseController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
