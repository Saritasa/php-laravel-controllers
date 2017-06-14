<?php

namespace Saritasa\Laravel\Controllers\Api;

use App\Models\User;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Validation\Validator;
use Saritasa\DingoApi\Exceptions\ValidationException;
use Saritasa\Transformers\BaseTransformer;
use Saritasa\Transformers\IDataTransformer;

/**
 * Base API controller, utilizing helpers from Dingo/API package
 *
 * @property User $user
 */
abstract class BaseApiController extends Controller
{
    use Helpers, ValidatesRequests;

    /**
     * @var IDataTransformer
     */
    protected $transformer;

    /**
     * Base API controller, utilizing helpers from Dingo/API package
     *
     * @param IDataTransformer $transformer - default transformer to apply to handled entity.
     * If not provided, BaseTransformer is used
     *
     * @see BaseTransformer - default transformer
     */
    public function __construct(IDataTransformer $transformer = null)
    {
        $this->transformer = $transformer ?: new BaseTransformer();
    }

    /**
     * Shortcut for work with Dingo/Api $this->response methods
     *
     * @param $data - Model or collection to be returned in response
     * @param IDataTransformer|null $transformer Transformer to use.
     * If omitted, default transformer for this controller will be used.
     * * @return Response
     */
    protected function json($data, IDataTransformer $transformer = null): Response
    {
        $t = $transformer ?: $this->transformer;
        if ($data instanceof Paginator) {
            return $this->response->paginator($data, $t);
        }
        return $this->response->item($data, $t);
    }

    /**
     * Validates request and throws exception, if input data in request doesn't match expected rules
     *
     * @param Request $request - Dingo/Api request (unlike in generic Laravel Controller)
     * @param array $rules - Laravel-style rules for validation https://laravel.com/docs/validation
     * @param array $messages
     * @param array $customAttributes
     *
     * @throws ValidationException - when input data in request does not match expected rules
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        /** @var Validator $validator */
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
    }
}
