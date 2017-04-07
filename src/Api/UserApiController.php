<?php

namespace App\Api\V1\Controllers;

use App\Models\User;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Saritasa\Api\Controllers\EntityApiController;
use Saritasa\Transformers\BaseTransformer;

/**
 * User resource representation.
 */
class UserApiController extends EntityApiController
{
    /**
     * Construct method. Define repo of resource
     *
     * @param BaseTransformer $transformer adds some info from user preferences
     */
    public function __construct(BaseTransformer $transformer)
    {
        parent::__construct(User::class, $transformer);
    }

    public function create(Request $request): Response
    {
        $this->validateCreateRequest($request);

        /* @var User $user */
        $user = new $this->modelClass($request->all());
        $rawPassword = $request->get('password');
        $user->password = $rawPassword;
        $user = $this->repo->create($user);
        $token = \JWTAuth::attempt(['email' => $user->email, 'password' => $rawPassword]);
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
