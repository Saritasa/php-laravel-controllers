<?php

namespace Saritasa\LaravelControllers\Api;

use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Saritasa\Transformers\BaseTransformer;
use Saritasa\Transformers\IDataTransformer;

/**
 * Base API controller, utilizing helpers from Dingo/API package.
 */
abstract class BaseApiController extends Controller
{
    use Helpers, AuthorizesRequests;

    /**
     * Default Fractal/Transformer instance to use
     *
     * @var IDataTransformer
     */
    protected $transformer;

    /**
     * Base API controller, utilizing helpers from Dingo/API package.
     *
     * @param IDataTransformer $transformer default transformer to apply to handled entity.
     * If not provided, BaseTransformer is used
     *
     * @see BaseTransformer - default transformer
     */
    public function __construct(IDataTransformer $transformer = null)
    {
        $this->transformer = $transformer ?: new BaseTransformer();
    }

    /**
     * Shortcut for work with Dingo/Api $this->response methods.
     *
     * @param mixed $data Model or collection to be returned in response
     * @param IDataTransformer $transformer Transformer to use.
     * If omitted, default transformer for this controller will be used.
     *
     * @return Response
     */
    protected function json($data, IDataTransformer $transformer = null): Response
    {
        $t = $transformer ?: $this->transformer;
        if ($data instanceof Paginator) {
            return $this->response->paginator($data, $t);
        }
        return $this->response->item($data, $t);
    }
}
