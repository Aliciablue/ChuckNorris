<?php

namespace App\Services;

use Throwable;
use App\Jobs\SaveSearchJob;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\ApiServiceException;
use App\Contracts\JobDispatcherInterface;
use App\Contracts\SearchServiceInterface;
use App\Contracts\SearchRepositoryInterface;

class SearchService implements SearchServiceInterface
{
    protected $apiUrl = 'https://api.chucknorris.io/jokes/';
    protected $categoriesCacheKey = 'chuck_norris_categories';
    protected $cacheTimeInSeconds = 60 * 60 * 24; //Remember cache for 1 day, could be increased connsiderably as site seems static
    protected $searchResultsCachePrefix = 'search_results_full_';
    protected $maxRetries = 3;
    protected $retryDelaySeconds = 1;

    public function __construct(protected SearchRepositoryInterface $searchRepository, protected JobDispatcherInterface $jobDispatcher, protected LoggerInterface $logger, protected Request $request) {}

    /**
     * Executes an API request with retry logic.
     *
     * @param string $url
     * @param callable $apiCall
     * @return mixed
     * @throws ApiServiceException
     */
    private function makeApiRequestWithRetries(string $url, callable $apiCall)
    {
        $retryDelay = $this->retryDelaySeconds;
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = $apiCall();

                if ($response->successful()) {
                    return $response;
                } elseif ($response->status() === 429) {
                    $this->logger->warning("Rate limit hit on {$url} (attempt {$attempt}). Retrying in {$retryDelay} seconds.");
                    sleep($retryDelay);
                    $retryDelay *= 2;
                } else {
                    $this->logger->error("Error fetching from API (Status: {$response->status()}, URL: {$url}): " . $response->body());
                    throw new ApiServiceException('Error al obtener datos desde la API.', $response->status());
                }
            } catch (Throwable $e) {
                $this->logger->error("Exception during API request to {$url} (attempt {$attempt}): " . $e->getMessage());
                if ($attempt < $this->maxRetries) {
                    $this->logger->warning("Retrying in {$retryDelay} seconds.");
                    sleep($retryDelay);
                    $retryDelay *= 2;
                } else {
                    throw new ApiServiceException('Error al conectar con la API después de varios intentos.', 0, $e);
                }
            }
        }

        $this->logger->error("Failed to fetch from {$url} after {$this->maxRetries} attempts.");
        throw new ApiServiceException('Error al obtener datos desde la API después de varios intentos por problemas con la API.', 500);
    }
    /**
     * Fetches Chuck Norris facts from the API based on the search type and query.
     *
     * @param array $data
     * @return array
     * @throws ApiServiceException
     */
    public function searchFacts(array $data)
    {
        $type = $data['type'];
        $query = $data['query'] ?? null;
        $this->logger->info("Searching facts: Type={$type}, Query={$query}");
        $url = '';

        if ($type === 'keyword' && $query) {
            $url = $this->apiUrl . 'search?query=' . urlencode($query) . "&page-=1";
        } elseif ($type === 'category' && $query) {
            $url = $this->apiUrl . 'random?category=' . urlencode($query);
        } elseif ($type === 'random') {
            $this->logger->info('Searching random fact');
            $url = $this->apiUrl . 'random';
        }

        if (!$url) {
            return [];
        }

        $response = $this->makeApiRequestWithRetries($url, function () use ($url) {
            return Http::get($url);
        });

        return $response->json()['result'] ?? [$response->json()];
    }
    /**
     * Retrieves all search results, either from cache or by fetching from the API.
     *
     * @param array $validatedData
     * @param string $type
     * @param string|null $query
     * @param string|null $email
     * @return array
     * @throws ApiServiceException
     */
    public function getAllResults($validatedData)
    {

        $type = $validatedData['type'];
        $query = $validatedData['query'] ?? null;

        $cacheKey = $this->searchResultsCachePrefix . md5(serialize("{$type} _ {$query}"));

        return Cache::remember(
            $cacheKey,
            $this->cacheTimeInSeconds,
            function () use ($validatedData, $type, $query) {
                $this->logger->info("Fetching and caching results: Type={$type}, Query={$query}");
                // Fetch results from the API
                return $this->searchFacts($validatedData);
            }
        );
    }
    /**
     * Retrieves Chuck Norris categories, either from cache or by fetching from the API with rate limit handling.
     *
     * @return array
     * @throws ApiServiceException
     */
    public function getCategories(): array
    {
        $this->logger->info('Checking cache for categories: ' . $this->categoriesCacheKey);
        $url = $this->apiUrl . 'categories';

        return Cache::remember(
            $this->categoriesCacheKey,
            $this->cacheTimeInSeconds,
            function () use ($url) {
                $this->logger->info('Fetching categories from API');
                $response = $this->makeApiRequestWithRetries($url, function () use ($url) {
                    return Http::get($url);
                });
                return $response->json();
            }
        );
    }
    public function getRandomChuckNorrisJoke()
    {
        $randomJokeData = $this->getAllResults(['type' => 'keyword', 'query' => 'Chuck']);
        if (count($randomJokeData) > 0) {
            return $randomJokeData[array_rand($randomJokeData)]['value'] ?? null;
        }
        return null; // Or a default joke/message
    }
    public function handleSearchRecord(string $type, ?string $query, array $allResults, ?string $email): void
    {
        if (!$this->request->attributes->get('current_search_initiated')) {
            try {
                // Create a new instance of the SaveSearchJob
                $job = new SaveSearchJob($this->searchRepository, $type, $query, $allResults, $email);

                // Dispatch the job using the injected JobDispatcher
                $this->jobDispatcher->dispatch($job);

                  // Mark that the job has been initiated for this request
                  $this->request->attributes->set('current_search_initiated', true); 
                  $this->logger->info('Search job dispatched successfully.', [
                    'type' => $type,
                    'query' => $query,
                    'email' => $email,
                ]);       
            } catch (\Exception $e) {
                // Log the error
                $this->logger->error('Error dispatching SaveSearchJob:', [
                    'message' => $e->getMessage(),
                    'type' => $type,
                    'query' => $query,
                    'email' => $email,
                ]);

                // Implement retry mechanisms or other error handling strategies here
                // Notify an administrator if job dispatch consistently fails.
            }
        }
    }
}
