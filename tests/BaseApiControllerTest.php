<?php

namespace Saritasa\Laravel\Controllers\Tests;

use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Dingo\Api\Http\Response\Factory as ResponseFactory;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\MessageBag;
use Mockery\MockInterface;
use Saritasa\DingoApi\Exceptions\ValidationException;
use Saritasa\Laravel\Controllers\Api\BaseApiController;
use Saritasa\Transformers\BaseTransformer;
use Saritasa\Transformers\IDataTransformer;

class BaseApiControllerTest extends TestCase
{
    /** @var MockInterface */
    protected $transformer;

    public function setUp()
    {
        parent::setUp();
        $this->transformer = \Mockery::mock(IDataTransformer::class);
    }

    public function testJsonItemResponseWithMethodTransformer()
    {
        $data = [
            'tempData' => str_random(),
        ];

        $expectedResponse = new Response(json_encode($data));

        $baseApiController = \Mockery::mock(BaseApiController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $responseFactoryMock = \Mockery::mock(ResponseFactory::class);
        $responseFactoryMock
            ->shouldReceive('item')
            ->withArgs([$data, $this->transformer])
            ->andReturn($expectedResponse);

        $baseApiController
            ->shouldReceive('response')
            ->andSet('response', $responseFactoryMock)
            ->andReturn($responseFactoryMock);

        $actualResponse = $baseApiController->json($data, $this->transformer);

        $this->assertEquals($expectedResponse, $actualResponse);
    }

    public function testJsonItemResponseWithClassTransformer()
    {
        $data = [
            'tempData' => str_random(),
        ];

        $expectedResponse = new Response(json_encode($data));

        $baseApiController = \Mockery::mock(BaseApiController::class, [$this->transformer])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $responseFactoryMock = \Mockery::mock(ResponseFactory::class);
        $responseFactoryMock
            ->shouldReceive('item')
            ->withArgs([$data, $this->transformer])
            ->andReturn($expectedResponse);

        $baseApiController
            ->shouldReceive('response')
            ->andSet('response', $responseFactoryMock)
            ->andReturn($responseFactoryMock);

        $actualResponse = $baseApiController->json($data);

        $this->assertEquals($expectedResponse, $actualResponse);
    }

    public function testJsonItemResponseWithDefaultTransformer()
    {
        $expectedData = [
            'tempData' => str_random(),
        ];

        $expectedResponse = new Response(json_encode($expectedData));

        $baseApiController = \Mockery::mock(BaseApiController::class, [null])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $responseFactoryMock = \Mockery::mock(ResponseFactory::class);
        $responseFactoryMock
            ->shouldReceive('item')
            ->andReturnUsing(function (array $data, IDataTransformer $baseTransformer) use (
                $expectedResponse,
                $expectedData
            ) {
                $this->assertTrue($baseTransformer instanceof BaseTransformer);
                $this->assertEquals($data, $expectedData);
                return $expectedResponse;
            });

        $baseApiController
            ->shouldReceive('response')
            ->andSet('response', $responseFactoryMock)
            ->andReturn($responseFactoryMock);

        $actualResponse = $baseApiController->json($expectedData);

        $this->assertEquals($expectedResponse, $actualResponse);
    }

    public function testJsonPaginatorWithDefaultTransformer()
    {
        $expectedPaginator = new Paginator([], 10);

        $expectedResponse = new Response(json_encode(['some string']));

        $baseApiController = \Mockery::mock(BaseApiController::class, [null])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $responseFactoryMock = \Mockery::mock(ResponseFactory::class);
        $responseFactoryMock
            ->shouldReceive('paginator')
            ->andReturnUsing(function (Paginator $actualPaginator, IDataTransformer $baseTransformer) use (
                $expectedResponse,
                $expectedPaginator
            ) {
                $this->assertTrue($baseTransformer instanceof BaseTransformer);
                $this->assertEquals($actualPaginator, $expectedPaginator);
                return $expectedResponse;
            });

        $baseApiController
            ->shouldReceive('response')
            ->andSet('response', $responseFactoryMock)
            ->andReturn($responseFactoryMock);

        $actualResponse = $baseApiController->json($expectedPaginator);

        $this->assertEquals($expectedResponse, $actualResponse);
    }

    public function testValidateSuccess()
    {
        $requestAsArray = $this->getRandomArray();
        $rules = $this->getRandomArray();
        $messages = $this->getRandomArray();
        $customAttributes = $this->getRandomArray();

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')->withArgs([])->andReturn($requestAsArray);

        $validator = \Mockery::mock(Validator::class);
        $validator->shouldReceive('fails')->withArgs([])->andReturnFalse();

        $validationFactory = \Mockery::mock(Factory::class);
        $validationFactory
            ->shouldReceive('make')
            ->withArgs([$requestAsArray, $rules, $messages, $customAttributes])
            ->andReturn($validator);

        $baseApiController = \Mockery::mock(BaseApiController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $baseApiController
            ->shouldReceive('getValidationFactory')
            ->withArgs([])
            ->andReturn($validationFactory);
        $actualResult = $baseApiController->validate($request, $rules, $messages, $customAttributes);
        $this->assertNull($actualResult);
    }

    public function testValidateFailure()
    {
        Mocks::mockTranslator(str_random());
        $requestAsArray = $this->getRandomArray();
        $rules = $this->getRandomArray();
        $messages = $this->getRandomArray();
        $customAttributes = $this->getRandomArray();
        $errors = new MessageBag($this->getRandomArray());

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')->withArgs([])->andReturn($requestAsArray);

        $validator = \Mockery::mock(Validator::class);
        $validator->shouldReceive('fails')->withArgs([])->andReturnTrue();
        $validator->shouldReceive('errors')->withArgs([])->andReturn($errors);

        $validationFactory = \Mockery::mock(Factory::class);
        $validationFactory
            ->shouldReceive('make')
            ->withArgs([$requestAsArray, $rules, $messages, $customAttributes])
            ->andReturn($validator);

        $this->expectExceptionObject(new ValidationException($errors));

        $baseApiController = \Mockery::mock(BaseApiController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $baseApiController
            ->shouldReceive('getValidationFactory')
            ->withArgs([])
            ->andReturn($validationFactory);
        $baseApiController->validate($request, $rules, $messages, $customAttributes);
    }

    protected function getRandomArray(): array
    {
        return range(rand(0, 100), rand(100, 200));
    }
}
