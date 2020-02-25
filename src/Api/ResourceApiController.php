<?php

namespace Saritasa\LaravelControllers\Api;

use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Saritasa\DingoApi\Traits\PaginatedOutput;
use Saritasa\Enums\PagingType;
use Saritasa\Exceptions\InvalidEnumValueException;
use Saritasa\LaravelEntityServices\Contracts\IEntityService;
use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceException;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceOperationException;
use Saritasa\LaravelRepositories\DTO\SortOptions;
use Saritasa\LaravelRepositories\Exceptions\BadCriteriaException;
use Saritasa\LaravelRepositories\Exceptions\ModelNotFoundException;
use Saritasa\LaravelRepositories\Exceptions\RepositoryException;
use Saritasa\Transformers\IDataTransformer;

/**
 * Default controller to handle CRUD operations with  managed models.
 */
class ResourceApiController extends BaseApiController
{
    use PaginatedOutput;

    /**
     * Factory that build entity services for managed model.
     *
     * @var IEntityServiceFactory
     */
    protected $entityServiceFactory;

    /**
     * Entity service to add CRUD operations with managed by this controller model.
     *
     * @var IEntityService
     */
    protected $entityService;

    /**
     * Managed model class.
     *
     * @var string|null
     */
    protected $modelClass = null;

    /**
     * Pagination type.
     *
     * @see PagingType
     *
     * @var string
     */
    protected $paging = PagingType::NONE;

    /**
     * Field used for sorting by default.
     *
     * @var string
     */
    protected $sortField = 'id';

    /**
     * Default controller to handle CRUD operation with models.
     *
     * @param IEntityServiceFactory $entityServiceFactory Entities services factory
     * @param IDataTransformer|null $transformer Default data transformer
     *
     * @throws EntityServiceException
     * @throws BindingResolutionException
     */
    public function __construct(IEntityServiceFactory $entityServiceFactory, ?IDataTransformer $transformer = null)
    {
        parent::__construct($transformer);
        $this->entityService = $entityServiceFactory->build($this->modelClass);
        $this->entityServiceFactory = $entityServiceFactory;
    }

    /**
     * Returns models collection by given params.
     *
     * @param Request $request Request with pagination and sorting info
     *
     * @return Response
     *
     * @throws InvalidEnumValueException
     * @throws BadCriteriaException
     */
    public function index(Request $request): Response
    {
        $searchValues = $request->only($this->entityService->getRepository()->getSearchableFields());

        switch ($this->paging) {
            case PagingType::PAGINATOR:
                return $this->response->paginator(
                    $this->entityService->getRepository()->getPage($this->readPaging($request), $searchValues),
                    $this->transformer
                );
            case PagingType::CURSOR:
                return $this->response->item(
                    $this->entityService->getRepository()->getCursorPage($this->readCursor($request), $searchValues),
                    $this->transformer
                );
            default:
                $sortOptions = new SortOptions($this->sortField);
                return $this->response->collection(
                    $this->entityService->getRepository()->getWith([], [], $searchValues, $sortOptions),
                    $this->transformer
                );
        }
    }

    /**
     * Creates new model.
     *
     * @param Request $request Request with new model params
     *
     * @return Response
     *
     * @throws EntityServiceOperationException
     * @throws ValidationException
     */
    public function store(Request $request): Response
    {
        return $this->json($this->entityService->create($request->toArray()), $this->transformer);
    }

    /**
     * Shows entity.
     *
     * @param int $id Entity ID
     *
     * @return Response
     * @throws ModelNotFoundException
     * @throws RepositoryException
     */
    public function show(int $id): Response
    {
        $model = $this->entityService->getRepository()->findOrFail($id);
        return $this->response->item($model, $this->transformer);
    }

    /**
     * Updates entity.
     *
     * @param Request $request Request with model params to update
     * @param int $id Entity ID
     * @return Response
     *
     * @throws EntityServiceException
     * @throws EntityServiceOperationException
     * @throws ModelNotFoundException
     * @throws RepositoryException
     * @throws ValidationException
     */
    public function update(Request $request, int $id): Response
    {
        $model = $this->entityService->getRepository()->findOrFail($id);
        $this->entityService->update($model, $request->toArray());
        return $this->response->item($model, $this->transformer);
    }

    /**
     * Destroys entity.
     *
     * @param int $id Entity ID
     *
     * @return Response
     *
     * @throws EntityServiceOperationException
     * @throws EntityServiceException
     */
    public function destroy(int $id): Response
    {
        $model = $this->entityService->getRepository()->findOrFail($id);
        $this->entityService->delete($model);
        return $this->response->noContent();
    }
}
