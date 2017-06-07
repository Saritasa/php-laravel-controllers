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

    public function __construct(IDataTransformer $transformer = null)
    {
        $this->transformer = $transformer ?: new BaseTransformer();
    }

    protected function json($data, IDataTransformer $transformer = null): Response
    {
        $t = $transformer ?: $this->transformer;
        if ($data instanceof Paginator) {
            return $this->response->paginator($data, $t);
        }
        return $this->response->item($data, $t);
    }

    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        /** @var Validator $validator */
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
    }
}
