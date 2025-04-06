<?php

namespace Tests\Unit\Repositories;

use Mockery;
use Tests\TestCase;
use App\Models\Search;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use App\Repositories\EloquentSearchRepository;
use App\Exceptions\EloquentSearchSaveException;
use Database\Factories\SearchFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Monolog\Logger;
use Psr\Log\LoggerInterface; 

class EloquentSearchRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected EloquentSearchRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        // Set up the EloquentSearchRepository with the Search model
        // This assumes that the Search model is already set up correctly
       
        $this->repository = new EloquentSearchRepository(   
            new Search(), new Logger('')
        );
    }
    #[Test]
    public function test_save_creates_search_entry()
    {
        $loggerMock = Mockery::mock(LoggerInterface::class);
        $loggerMock->shouldReceive('info')->once()->with('Search results saved. testType | testQuery');
    
        $repository = new EloquentSearchRepository(new Search(), $loggerMock);
    
        $search = $repository->save('testType', 'testQuery', ['result1', 'result2'], 'test@example.com');
    
        $this->assertInstanceOf(Search::class, $search);
    
        $this->assertDatabaseHas('searches', [
            'type' => 'testType',
            'query' => 'testQuery',
            'results' => json_encode(['result1', 'result2']),
            'email' => 'test@example.com',
        ]);
    }
    #[Test]
public function test_save_throws_exception_on_failure()
{
    $this->expectException(EloquentSearchSaveException::class);

    // Mock the Search model
    $mock = Mockery::mock(Search::class);
    $mock->shouldReceive('newInstance')->once()->andReturnSelf();
    $mock->shouldReceive('save')->once()->andReturnFalse();
    $mock->exists = false;

    // Mock the logger
    $loggerMock = Mockery::mock(LoggerInterface::class);
    $loggerMock->shouldNotReceive('info'); // nothing should be logged if it fails

    // Inject both mocks
    $repository = new EloquentSearchRepository($mock, $loggerMock);

    $repository->save('testType', 'testQuery', ['result1'], 'test@example.com');
}
}
