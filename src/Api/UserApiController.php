<?php

namespace Saritasa\Laravel\Controllers\Api;

use App\Models\User;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Saritasa\Api\Controllers\EntityApiController;
use Saritasa\Laravel\Controllers\Services\AuthJWTService;
use Saritasa\Transformers\BaseTransformer;

/**
 * User resource representation.
 */
class UserApiController extends EntityApiController
{
    private $authService;

    /**
     * Construct method. Define repo of resource
     *
     * @param BaseTransformer $transformer adds some info from user preferences
     */
    public function __construct(BaseTransformer $transformer, AuthJWTService $authService)
    {
        parent::__construct(User::class, $transformer);
        $this->authService = $authService;
    }

    public function create(Request $request): Response
    {
        $this->validateCreateRequest($request);

        /* @var User $user */
        $user = new $this->modelClass($request->all());
        $rawPassword = $request->get('password');
        $user->password = $rawPassword;
        $user = $this->repo->create($user);
        $token = $this->authService->auth($user->email, $rawPassword);
        return $this->json(collect(compact('user', 'token')));
    }

    /**
     * Show authenticated user details
     *
     * @return \Dingo\Api\Http\Response
     */
    public function me()
    {
        /* @var User $user */
        $user = $this->user;
        return $this->json($user, $this->transformer);
    }

    public function updateMe(Request $request)
    {
        /* @var User $user */
        $user = $this->user;
        return $this->update($request, $user->id);
    }
}
