<?php

namespace Saritasa\Laravel\Controllers\Api;

use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

interface IApiResourceController
{
    public function create(Request $request): Response;
    public function show(Request $request, string $id): Response;
    public function index(Request $request): Response;
    public function update(Request $request, string $id): Response;
    public function destroy(Request $request, string $id): Response;
}
