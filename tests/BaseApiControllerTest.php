<?php

namespace Saritasa\LaravelControllers\Tests;

use Dingo\Api\Http\Request;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\MessageBag;
use Mockery;
use Mockery\MockInterface;
use Saritasa\DingoApi\Exceptions\ValidationException;
use Saritasa\LaravelControllers\Api\BaseApiController;
use Saritasa\Transformers\IDataTransformer;

/**
 * Tests for base api controller.
 */
class BaseApiControllerTest extends TestCase
{
    /**
     * Data transformer mock.
     *
     * @var MockInterface|IDataTransformer
     */
    protected $transformer;

    /**
     * Base api controller.
     *
     * @var MockInterface|BaseApiController
     */
    protected $baseApiController;

    /**
     * Default request instance.
     *
     * @var MockInterface|Request
     */
    protected $request;

    /**
     * Prepare tests for run.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->transformer = Mockery::mock(IDataTransformer::class);
        $this->baseApiController = Mockery::mock(BaseApiController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->request = Mockery::mock(Request::class);
    }

    /**
     * Test validation method when success.
     *
     * @return void
     */
    public function testValidateSuccess(): void
    {
        $requestAsArray = $this->getRandomArray();
        $rules = $this->getRandomArray();
        $messages = $this->getRandomArray();
        $customAttributes = $this->getRandomArray();

        $this->request->shouldReceive('all')->withArgs([])->andReturn($requestAsArray);

        $validator = Mockery::mock(Validator::class);
        $validator->shouldReceive('fails')->withArgs([])->andReturnFalse();

        $validationFactory = Mockery::mock(Factory::class);
        $validationFactory
            ->shouldReceive('make')
            ->withArgs([$requestAsArray, $rules, $messages, $customAttributes])
            ->andReturn($validator);

        $this->baseApiController
            ->shouldReceive('getValidationFactory')
            ->withArgs([])
            ->andReturn($validationFactory);
        $actualResult = $this->baseApiController->validate($this->request, $rules, $messages, $customAttributes);
        $this->assertNull($actualResult);
    }

    /**
     * Test validation method when data failure.
     *
     * @return void
     */
    public function testValidateFailure(): void
    {
        Mocks::mockTranslator(str_random());
        $requestAsArray = $this->getRandomArray();
        $rules = $this->getRandomArray();
        $messages = $this->getRandomArray();
        $customAttributes = $this->getRandomArray();
        $errors = new MessageBag($this->getRandomArray());

        $this->request->shouldReceive('all')->withArgs([])->andReturn($requestAsArray);

        $validator = Mockery::mock(Validator::class);
        $validator->shouldReceive('fails')->withArgs([])->andReturnTrue();
        $validator->shouldReceive('errors')->withArgs([])->andReturn($errors);

        $validationFactory = Mockery::mock(Factory::class);
        $validationFactory
            ->shouldReceive('make')
            ->withArgs([$requestAsArray, $rules, $messages, $customAttributes])
            ->andReturn($validator);

        $this->expectExceptionObject(new ValidationException($errors));

        $this->baseApiController
            ->shouldReceive('getValidationFactory')
            ->withArgs([])
            ->andReturn($validationFactory);
        $this->baseApiController->validate($this->request, $rules, $messages, $customAttributes);
    }

    /**
     * Return random array.
     *
     * @return array
     */
    protected function getRandomArray(): array
    {
        return range(rand(0, 100), rand(100, 200));
    }
}
