<?php

namespace Saritasa\Laravel\Controllers\Web;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface IWebResourceController
{
    /** Page with list of entities (usually paginated) */
    public function index(Request $request): View;

    /** AJAX handler to get list of entities (usually paginated) */
    public function indexData(Request $request): Response;

    /** Page for creating new entity */
    public function create(Request $request): View;

    /** AJAX-handler for saving new entity instance */
    public function store(Request $request): JsonResponse;

    /** Page displaying view existing record */
    public function show(Request $request, string $id): View;

    /** AJAX-handler to return data for existing record */
    public function read(Request $request, string $id): JsonResponse;

    /** Page for editing existing record */
    public function edit(Request $request, string $id): View;

    /** AJAX-handler to update existing record */
    public function update(Request $request, string $id): JsonResponse;

    /** AJAX-handler to delete existing record */
    public function destroy(Request $request, string $id): JsonResponse;
}
