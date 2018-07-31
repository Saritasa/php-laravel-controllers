<?php

namespace Saritasa\LaravelControllers\Api;

use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Saritasa\DingoApi\Traits\PaginatedOutput;
use Saritasa\Enums\PagingType;
use Saritasa\Exceptions\InvalidEnumValueException;
use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceException;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceOperationException;
use Saritasa\LaravelRepositories\DTO\SortOptions;
use Saritasa\Transformers\IDataTransformer;

/**
 * Default controller to handle CRUD operation with models.
 */
class ResourceApiController extends BaseApiController
{
    use PaginatedOutput;

    /**
     * Entities services factory.
     *
     * @var IEntityServiceFactory
     */
    protected $entityServiceFactory;

    /**
     * Entity service to work with serve by this controller model.
     *
     * @var IEntityServiceFactory
     */
    protected $entityService;

    /**
     * Serve model class.
     *
     * @var string|null
     */
    protected $modelClass = null;

    /**
     * Pagination type.
     *
     * @var string
     */
    protected $paging = PagingType::NONE;

    /**
     * Field uses for sorting.
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
     */
    public function __construct(IEntityServiceFactory $entityServiceFactory, IDataTransformer $transformer = null)
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
     */
    public function index(Request $request): Response
    {
        $searchValues = $request->only($this->entityService->getRepository()->searchableFields);

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
    public function create(Request $request): Response
    {
        return $this->json($this->entityService->create($request->toArray()), $this->transformer);
    }

    /**
     * Shows entity.
     *
     * @param Model $model Model to show
     *
     * @return Response
     */
    public function show(Model $model): Response
    {
        return $this->response->item($model, $this->transformer);
    }

    /**
     * Updates entity.
     *
     * @param Request $request Request with update model params
     * @param Model $model Model to update
     *
     * @return Response
     *
     * @throws EntityServiceOperationException
     * @throws ValidationException
     */
    public function update(Request $request, Model $model): Response
    {
        $this->entityService->update($model, $request->toArray());
        return $this->response->item($model, $this->transformer);
    }

    /**
     * Destroys entity.
     *
     * @param Model $model Model to delete
     *
     * @return Response
     *
     * @throws EntityServiceOperationException
     */
    public function destroy(Model $model): Response
    {
        $this->entityService->delete($model);
        return $this->response->noContent();
    }
}
