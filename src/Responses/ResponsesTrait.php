<?php

namespace Saritasa\LaravelControllers\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Train create different responses.
 */
trait ResponsesTrait
{
    /**
     * Create response with Accepted status.
     *
     * @param JsonResource|null $resource Resource for creating response
     *
     * @return Response
     */
    public function responseAccepted(?JsonResource $resource = null): Response
    {
        return $this->createResponse($resource, SymfonyResponse::HTTP_ACCEPTED);
    }

    /**
     * Create response with OK status.
     *
     * @param JsonResource|null $resource Resource for creating response
     *
     * @return Response
     */
    public function responseOk(?JsonResource $resource = null): Response
    {
        return $this->createResponse($resource);
    }

    /**
     * Create response with Created status.
     *
     * @param JsonResource|null $resource Resource for creating response
     *
     * @return Response
     */
    public function responseCreated(?JsonResource $resource = null): Response
    {
        return $this->createResponse($resource, SymfonyResponse::HTTP_CREATED);
    }

    /**
     * Create response with No Content status.
     *
     * @return Response
     */
    public function responseNoContent(): Response
    {
        return $this->createResponse(null, SymfonyResponse::HTTP_NO_CONTENT);
    }

    public function errorUnauthorized(?string $message = null): JsonResponse
    {
        return new JsonResponse(new ErrorMessage($message), SymfonyResponse::HTTP_UNAUTHORIZED);
    }

    public function errorForbidden(?string $message = null): JsonResponse
    {
        return new JsonResponse(new ErrorMessage($message), SymfonyResponse::HTTP_FORBIDDEN);
    }

    public function errorNotFound(?string $message = null): JsonResponse
    {
        return new JsonResponse(new ErrorMessage($message), SymfonyResponse::HTTP_NOT_FOUND);
    }

    public function errorInternal(?string $message): JsonResponse
    {
        return new JsonResponse(new ErrorMessage($message), SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function json($data): JsonResponse
    {
        return new JsonResponse($data);
    }

    /**
     * Create response for resource.
     *
     * @param JsonResource|null $resource Resource for creating response
     * @param int $status Response status
     * @param array<string, string> $headers Response header
     *
     * @return Response
     */
    public function createResponse(
        ?JsonResource $resource,
        int $status = SymfonyResponse::HTTP_OK,
        array $headers = []
    ): Response {
        if ($resource instanceof JsonResource && is_array($resource->resource)) {
            $resource->resource = array_merge(['status' => 'success'], $resource->resource);
        }

        return $this->createIlluminateResponse($resource?->toJson(), $status, $headers);
    }

    /**
     * Create response.
     *
     * @param mixed $content Content for creating response
     * @param int $status Response status
     * @param array<string, string> $headers Response header
     *
     * @return Response
     */
    private function createIlluminateResponse(mixed $content, int $status, array $headers = []): Response
    {
        $headers = array_merge(['Content-Type' =>'application/json'], $headers);

        return new Response($content, $status, $headers);
    }
}
