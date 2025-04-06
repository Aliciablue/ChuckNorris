<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Psr\Log\LoggerInterface;

class SearchCacheService
{
    protected $categoriesCacheKey = 'chuck_norris_categories';
    protected $searchResultsCachePrefix = 'search_results_full_';
    protected $cacheTimeInSeconds = 60 * 60 * 24; // Remember cache for 1 day

    public function __construct(protected LoggerInterface $logger) {}

    /**
     * Retrieves Chuck Norris categories from the cache.
     *
     * @return array|null
     */
    public function getCategoriesFromCache(): ?array
    {
        $this->logger->info('Checking cache for categories: ' . $this->categoriesCacheKey);
        return Cache::get($this->categoriesCacheKey);
    }

    /**
     * Stores Chuck Norris categories in the cache.
     *
     * @param array $categories
     * @return void
     */
    public function storeCategoriesInCache(array $categories): void
    {
        $this->logger->info('Storing categories in cache: ' . $this->categoriesCacheKey);
        Cache::put($this->categoriesCacheKey, $categories, $this->cacheTimeInSeconds);
    }

    /**
     * Retrieves search results from the cache.
     *
     * @param string $type
     * @param string|null $query
     * @return array|null
     */
    public function getSearchResultsFromCache(string $type, ?string $query): ?array
    {
        $cacheKey = $this->searchResultsCachePrefix . md5(serialize("{$type} _ {$query}"));
        $this->logger->info("Checking cache for search results: {$cacheKey}");
        return Cache::get($cacheKey);
    }

    /**
     * Stores search results in the cache.
     *
     * @param array $results
     * @param string $type
     * @param string|null $query
     * @return void
     */
    public function storeSearchResultsInCache(array $results, string $type, ?string $query): void
    {
        $cacheKey = $this->searchResultsCachePrefix . md5(serialize("{$type} _ {$query}"));
        $this->logger->info("Storing search results in cache: {$cacheKey}");
        Cache::put($cacheKey, $results, $this->cacheTimeInSeconds);
    }
}