<?php

namespace Saritasa\Laravel\Controllers\Api;

use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

interface IApiResourceController
{
    function create(Request $request): Response;
    function show(Request $request, string $id): Response;
    function index(Request $request): Response;
    function update(Request $request, string $id): Response;
    function destroy(Request $request, string $id): Response;
}
