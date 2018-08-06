<?php

namespace Saritasa\LaravelControllers;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Custom base controller for Saritasa application
 * Authorizes requests and validates requests
 */
class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
}
