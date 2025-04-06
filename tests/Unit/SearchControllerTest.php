<?php

namespace Tests\Unit\Controllers;

use Mockery;
use Tests\TestCase;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SearchRequest;
use Illuminate\Http\Response;
use App\Services\SearchRecordService;
use Illuminate\Http\RedirectResponse;
use App\Exceptions\ApiServiceException;
use App\Contracts\SearchServiceInterface;
use App\Http\Controllers\SearchController;
use App\Contracts\SearchRepositoryInterface;
use App\Contracts\NotificationServiceInterface;
use App\Exceptions\EloquentSearchSaveException;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchControllerTest extends TestCase
{

    protected $searchService;
    protected $repository;
    protected $notification;
    protected $recordService;
    protected $logger;
    protected SearchController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchService = Mockery::mock(SearchServiceInterface::class);
        $this->repository = Mockery::mock(SearchRepositoryInterface::class);
        $this->notification = Mockery::mock(NotificationServiceInterface::class);
        $this->recordService = Mockery::mock(SearchRecordService::class);

        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->logger->shouldAllowMockingProtectedMethods();
        $this->logger->shouldReceive('error')->andReturnNull();

        $this->controller = new SearchController(
            $this->searchService,
            $this->repository,
            $this->notification,
            $this->recordService,
            $this->logger
        );
    }
    public function test_index_returns_joke()
    {
        // Arrange
        $this->searchService->shouldReceive('getRandomChuckNorrisJoke')->andReturn(['value' => 'Chuck Norris joke']);

        // Act
        $locale = 'en'; // or 'es', depending on the language
        $response = $this->get(route('search.index', ['locale' => $locale]));

        // Assert: Check if the view contains the joke
       $response->assertViewHas('randomJoke');
   
    }

    public function test_search_returns_json_response_with_paginated_results()
    {
        // Simulate validated request input
        $validatedData = [
            'type' => 'keyword',
            'query' => 'chuck',
            'email' => 'test@example.com'
        ];

        // Create mock SearchRequest
        $request = Mockery::mock(SearchRequest::class);
        $request->shouldReceive('validated')->andReturn($validatedData);
        $request->shouldReceive('get')->with('page', 1)->andReturn(1);
        $request->shouldReceive('get')->with('page')->andReturn(1);
        $request->shouldReceive('wantsJson')->andReturn(true);
        $request->shouldReceive('url')->andReturn('/search');
        $request->shouldReceive('query')->andReturn([]);

        // Mock search result
        $searchResults = [
            ['value' => 'Chuck Norris counted to infinity. Twice.'],
            ['value' => 'Chuck Norris can divide by zero.']
        ];

        $this->searchService->shouldReceive('getAllResults')
            ->once()
            ->with($validatedData)
            ->andReturn($searchResults);

        $this->recordService->shouldReceive('handleSearchRecord')
            ->once()
            ->with('keyword', 'chuck', $searchResults, 'test@example.com');

        $this->notification->shouldReceive('handleSearchResultNotification')
            ->once()
            ->with(
                $request,
                Mockery::on(fn($array) => is_array($array) && count($array) <= 2),
                'keyword',
                'chuck',
                'test@example.com',
                2
            );

        // Act
        $response = $this->controller->search($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);  // Expecting a JsonResponse now
        $this->assertEquals(200, $response->status()); // Or the correct status code for success
    }
    public function test_search_handles_api_service_exception()
    {
        $request = Mockery::mock(SearchRequest::class);
        $request->shouldReceive('validated')->andReturn(['type' => 'keyword', 'query' => 'error']);
        $request->shouldReceive('get')->with('page', 1)->andReturn(1);
        $request->shouldReceive('wantsJson')->andReturn(false);  // Simulate a non-JSON request



        $this->searchService->shouldReceive('getAllResults')
            ->once()
            ->andThrow(new \App\Exceptions\ApiServiceException('API down'));

        $response = $this->controller->search($request);

        $this->assertInstanceOf(RedirectResponse::class, $response); // not JsonResponse
        $this->assertTrue($response->isRedirect());
    }
    public function test_search_handles_eloquent_search_save_exception()
    {
        $validatedData = ['type' => 'keyword', 'query' => 'fail', 'email' => 'user@example.com'];

        $request = Mockery::mock(SearchRequest::class);
        $request->shouldReceive('validated')->andReturn($validatedData);
        $request->shouldReceive('get')->with('page', 1)->andReturn(1);
        $request->shouldReceive('wantsJson')->andReturn(false);  // Simulate a non-JSON request


        $request->shouldReceive('url')->andReturn('/search');
        $request->shouldReceive('query')->andReturn([]);

        $searchResults = [['id' => 1, 'title' => 'Dummy result']];

        $this->searchService->shouldReceive('getAllResults')
            ->once()
            ->andReturn($searchResults);

        $this->recordService->shouldReceive('handleSearchRecord')
            ->once()
            ->andThrow(new \App\Exceptions\EloquentSearchSaveException('DB error'));

        $response = $this->controller->search($request);

        $this->assertInstanceOf(RedirectResponse::class, $response); // not JsonResponse
        $this->assertTrue($response->isRedirect());
    }
    public function test_categories_handles_api_service_exception()
    {
        // Arrange
        $this->searchService->shouldReceive('getCategories')->andThrow(new ApiServiceException('API down'));
        $this->app->instance(SearchServiceInterface::class, $this->searchService);
        // Act
        $locale = 'en'; // or 'es', depending on the language
        $response = $this->get(route('search.categories', ['locale' => $locale]));

        // Assert: Check if the correct error message is returned
        $response->assertStatus(503);
        $response->assertJson(['error' => 'Error al conectar con la API']);
    }

    public function test_categories_handles_generic_exception()
    {
        // Arrange
        $this->searchService->shouldReceive('getCategories')->andThrow(new \Exception('Generic error'));
        $this->app->instance(SearchServiceInterface::class, $this->searchService);

        // Act: Include the 'locale' parameter in the URL
        $locale = 'en'; // or 'es', depending on the language
        $response = $this->get(route('search.categories', ['locale' => $locale]));

        // Assert: Check if the correct error message is returned
        $response->assertStatus(500);
        $response->assertJson(['error' => 'Error al obtener las categorías']);
    }


    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
