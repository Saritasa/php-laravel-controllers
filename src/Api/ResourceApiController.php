<?php

namespace Saritasa\Laravel\Controllers\Api;

use Dingo\Api\Http\FormRequest;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\Model;
use Saritasa\Contracts\IRepositoryFactory;
use Saritasa\DingoApi\Traits\PaginatedOutput;
use Saritasa\DTO\SortOptions;
use Saritasa\Enums\PagingType;
use Saritasa\Exceptions\RepositoryException;
use Saritasa\Laravel\Contracts\IResourceController;
use Saritasa\Transformers\IDataTransformer;

class ResourceApiController extends BaseApiController implements IResourceController
{
    use PaginatedOutput;

    protected $repositoryFactory;

    protected $modelClass = null;

    protected $paging = PagingType::NONE;

    /**
     * Field uses for sorting.
     *
     * @var string
     */
    protected $sortField = 'id';

    public function __construct(IRepositoryFactory $repositoryFactory, IDataTransformer $transformer = null)
    {
        parent::__construct($transformer);
        $this->repositoryFactory = $repositoryFactory;
    }

    public function setModelClass(string $modelClass): void
    {
        $this->modelClass = $modelClass;
    }

    /**
     * Returns models collection by given params.
     *
     * @param FormRequest $request Current request
     *
     * @return Response
     *
     * @throws \Throwable
     */
    public function index(FormRequest $request): Response
    {
        $repository = $this->repositoryFactory->getRepository($this->modelClass);

        $searchValues = $request->only($repository->searchableFields);

        switch ($this->paging) {
            case PagingType::PAGINATOR:
                return $this->response->paginator(
                    $repository->getPage($this->readPaging($request), $searchValues),
                    $this->transformer
                );
            case PagingType::CURSOR:
                return $this->response->item(
                    $repository->getCursorPage($this->readCursor($request), $searchValues),
                    $this->transformer
                );
            default:
                $sortOptions = new SortOptions($this->sortField);
                return $this->response->collection(
                    $repository->getWith([], [], $searchValues, $sortOptions),
                    $this->transformer
                );
        }
    }

    /**
     * Creates new model.
     *
     * @param FormRequest $request Current request
     *
     * @return Response
     *
     * @throws RepositoryException
     */
    public function create(FormRequest $request): Response
    {
        $model = $this->repositoryFactory
            ->getRepository($this->modelClass)
            ->create(new $this->modelClass($request->toArray()));
        return $this->json($model, $this->transformer);
    }

    /**
     * Shows entity.
     *
     * @param Model $model
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
     * @param Request $request Current request
     * @param Model $model Model to update
     *
     * @return Response
     *
     * @throws RepositoryException
     */
    public function update(Request $request, Model $model): Response
    {
        $this->repositoryFactory->getRepository($this->modelClass)->save($model->fill($request->toArray()));
        return $this->response->item($model, $this->transformer);
    }

    /**
     * Destroys entity.
     *
     * @param Model $model Model to delete
     *
     * @return Response
     *
     * @throws RepositoryException
     */
    public function destroy(Model $model): Response
    {
        $this->repositoryFactory->getRepository($this->modelClass)->delete($model);
        return $this->response->noContent();
    }
}
