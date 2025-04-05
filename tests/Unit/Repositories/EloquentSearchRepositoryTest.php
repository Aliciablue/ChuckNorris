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
        Log::shouldReceive('info')->once();

        $search = $this->repository->save('testType', 'testQuery', ['result1', 'result2'], 'test@example.com');

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

        // Create a mock of the Search model
        $mock = Mockery::mock(Search::class);
        $mock->shouldReceive('newInstance')->once()->andReturnSelf(); // Return itself for new instance
        $mock->shouldReceive('save')->once()->andReturnFalse(); // Simulate failed save
        $mock->exists = false; // Ensure exists is false
    
        // Inject mock into repository
        $repository = new EloquentSearchRepository($mock);
    
        // Run test: should throw exception because save() fails
        $repository->save('testType', 'testQuery', ['result1'], 'test@example.com');
    }
}
