<?php

namespace App\Services;

use App\Exceptions\ApiServiceException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Contracts\SearchServiceInterface;
use App\Contracts\SearchRepositoryInterface;
use Illuminate\Support\Facades\Redis;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService implements SearchServiceInterface
{
    protected $apiUrl = 'https://api.chucknorris.io/jokes/';
    protected $categoriesCacheKey = 'chuck_norris_categories';
    protected $cacheTimeInSeconds = 60 * 60 * 24; //Remember cache for 1 day, could be increased connsiderably as site seems static
    protected $searchResultsCachePrefix = 'search_results_full_';

    public function __construct(protected SearchRepositoryInterface $searchRepository) {}
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
        Log::info("Searching facts: Type={$type}, Query={$query}");
        $results = [];


        if ($type === 'keyword' && $query) {
            $response = Http::get($this->apiUrl . 'search?query=' . urlencode($query) . "&page-=1");
            $results = $response->json()['result'] ?? [];
        } elseif ($type === 'category' && $query) {
            $response = Http::get($this->apiUrl . 'random?category=' . urlencode($query));
            $results = [$response->json()];
        } elseif ($type === 'random') {
            $response = Http::get($this->apiUrl . 'random');
            $results = [$response->json()];
        }
        if (empty($results)) {
            Log::warning("No results found for: Type={$type}, Query={$query}");
            throw new ApiServiceException('Error al obtener los resultados de la búsqueda.', 500);
        }

        //$this->setCacheResults('search_results_' . md5(serialize($data)), $results);

        return $results;
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
    public function getAllResults($validatedData, $type, $query, $email)
    {
        
        $cacheKey = $this->searchResultsCachePrefix . md5(serialize("{$type} _ {$query}"));

        return Cache::remember(
            $cacheKey,
            $this->cacheTimeInSeconds,
            function () use ($validatedData, $type, $query, $email) {
                Log::info("Fetching and caching results: Type={$type}, Query={$query}");
                // Fetch results from the API
                return $this->searchFacts($validatedData);  
            }
        );      
    }
/**
     * Retrieves Chuck Norris categories, either from cache or by fetching from the API.
     *
     * @return array
     * @throws ApiServiceException
     */
    public function getCategories(): array
    {

        Log::info('Checking cache for categories: ' . $this->categoriesCacheKey);

        return Cache::remember(
            $this->categoriesCacheKey,
            $this->cacheTimeInSeconds,
            function () {
                Log::info('Fetching categories from API');
                $response = Http::get($this->apiUrl . 'categories');
                if ($response->successful()) {
                    $categories = $response->json();
                    Log::info('Categories saved to cache with TTL: ' . ($this->cacheTimeInSeconds / 60 / 60 / 24) . ' days.');
                    return $categories;
                } else {
                    Log::error('Error fetching categories from API: ' . $response->status());
                    throw new ApiServiceException('Error al obtener las categorías desde la API.', $response->status());
                }
            }
        );
    }
}
