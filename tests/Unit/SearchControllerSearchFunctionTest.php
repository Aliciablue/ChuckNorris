<?php

namespace Tests\Unit\Http\Controllers;

use App\Contracts\NotificationServiceInterface;
use App\Contracts\SearchRepositoryInterface;
use App\Contracts\SearchServiceInterface;
use App\Http\Controllers\SearchController;
use App\Http\Requests\SearchRequest;
use App\Jobs\SaveSearchJob;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SearchControllerSearchFunctionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Session::flush(); // Clear session before each test
        App::setLocale('en'); // Ensure locale is English for URL generation in this test
    }

    public function test_search_method_calls_search_service_and_returns_results_view()
    {
        // Arrange
        $searchServiceMock = $this->createMock(SearchServiceInterface::class);
        $searchRepositoryMock = $this->createMock(SearchRepositoryInterface::class);
        $notificationServiceMock = $this->createMock(NotificationServiceInterface::class);

        $controller = new SearchController($searchServiceMock, $searchRepositoryMock, $notificationServiceMock);

        $requestData = [
            'type' => 'keyword',
            'query' => 'test',
        ];
        $request = new SearchRequest($requestData);
        $validator = Validator::make($requestData, $request->rules());
        $request->setValidator($validator);

        $searchResults = [['id' => 1, 'value' => 'Fact 1'], ['id' => 2, 'value' => 'Fact 2']];
        $searchServiceMock->expects($this->once())
            ->method('getAllResults')
            ->with($requestData, 'keyword', 'test', null)
            ->willReturn($searchResults);

        Session::shouldReceive('get')
            ->once()
            ->with('current_search_id') // Expecting only the key
            ->andReturn(null); // Simulate no existing search ID

        Session::shouldReceive('put')
            ->once()
            ->with('current_search_id', $this->callback(function ($value) {
                return is_string($value);
            })); // Expect a string as the ID

        // Act
        $response = $controller->search($request);

        // Assert
        $this->assertEquals('search.results', $response->getName());
        $this->assertInstanceOf(LengthAwarePaginator::class, $response->getData()['results']);
        $this->assertEquals('test', $response->getData()['searchTerm']);
        $this->assertEquals('keyword', $response->getData()['searchType']);
        $this->assertNotNull(session('current_search_id'));
    }

    public function test_search_method_calls_search_repository_to_save_results()
    {
        // Arrange
        $searchServiceMock = $this->createMock(SearchServiceInterface::class);
        $searchRepositoryMock = $this->createMock(SearchRepositoryInterface::class);
        $notificationServiceMock = $this->createMock(NotificationServiceInterface::class);

        $controller = new SearchController($searchServiceMock, $searchRepositoryMock, $notificationServiceMock);

        $requestData = [
            'type' => 'keyword',
            'query' => 'test',
        ];
        $request = new SearchRequest($requestData);
        $validator = Validator::make($requestData, $request->rules());
        $request->setValidator($validator);

        $searchResults = [['id' => 1, 'value' => 'Fact 1']];
        $searchServiceMock->expects($this->once())
            ->method('getAllResults')
            ->willReturn($searchResults);

        Queue::fake(); // To prevent actual job dispatch

        Session::shouldReceive('get')
            ->once()
            ->with('current_search_id') // Expecting only the key
            ->andReturn(null); // Simulate no existing search ID

        Session::shouldReceive('put')
            ->once()
            ->with('current_search_id', $this->callback(function ($value) {
                return is_string($value);
            })); // Expect a string as the ID

        // Act
        $controller->search($request);

        // Assert
        Queue::assertPushed(SaveSearchJob::class, function ($job) use ($searchResults) {
            return $job->type === 'keyword' && $job->query === 'test' && $job->results === $searchResults && $job->email === null;
        });
    }

    public function test_search_method_calls_notification_service_when_email_is_provided()
    {
        // Arrange
        $searchServiceMock = $this->createMock(SearchServiceInterface::class);
        $searchRepositoryMock = $this->createMock(SearchRepositoryInterface::class);
        $notificationServiceMock = $this->createMock(NotificationServiceInterface::class);

        $controller = new SearchController($searchServiceMock, $searchRepositoryMock, $notificationServiceMock);

        $requestData = [
            'type' => 'keyword',
            'query' => 'test',
            'email' => 'test@example.com',
        ];
        $request = new SearchRequest($requestData);
        $validator = Validator::make($requestData, $request->rules());
        $request->setValidator($validator);

        $searchResults = [['id' => 1, 'value' => 'Fact 1']];
        $searchServiceMock->expects($this->once())
            ->method('getAllResults')
            ->willReturn($searchResults);

        Queue::fake(); // To prevent actual job dispatch

        Session::shouldReceive('get')
            ->once()
            ->with('current_search_id') // Expecting only the key
            ->andReturn(null); // Simulate no existing search ID

        Session::shouldReceive('put')
            ->once()
            ->with('current_search_id', $this->callback(function ($value) {
                return is_string($value);
            })); // Expect a string as the ID

        $notificationServiceMock->expects($this->once())
            ->method('sendSearchResultsNotification')
            ->with(
                $searchResults,
                'keyword',
                'test',
                'test@example.com',
                $this->callback(function ($url) {
                    return str_contains($url, 'http://localhost/en/results?type=keyword&query=test');
                }),
                'en',
                1
            );

        Session::shouldReceive('flash')
            ->once()
            ->with('success', __('messages.email_sent_confirmation'));

        // Act
        $controller->search($request);

        // Assert
        $this->assertTrue(true); // Assertion is in the mock expectation
    }

    // ... (rest of the exception handling tests and categories tests - adapt as needed) ...
}