<?php

namespace App\Services;

use App\Services\SearchCacheService;
//use App\Services\SearchRecordService;
use Psr\Log\LoggerInterface;
use App\Contracts\SearchServiceInterface;

class ChuckNorrisSearchService implements SearchServiceInterface
{

    public function __construct(
        protected ChuckNorrisApiService $apiService,
        protected SearchCacheService $cacheService,
       // protected SearchRecordService $recordService,
        protected LoggerInterface $logger
    ) {}

    public function getAllResults($validatedData)
    {
        $type = $validatedData['type'];
        $query = $validatedData['query'] ?? null;

        return $this->cacheService->getSearchResultsFromCache($type, $query) ??
            tap($this->apiService->getFacts($type, $query), function ($results) use ($type, $query) {
                $this->cacheService->storeSearchResultsInCache($results, $type, $query);
            });
    }

    public function getCategories(): array
    {
        return $this->cacheService->getCategoriesFromCache() ??
            tap($this->apiService->getCategoriesFromApi(), function ($categories) {
                $this->cacheService->storeCategoriesInCache($categories);
            });
    }

    public function getRandomChuckNorrisJoke()
    {
        $randomJokeData = $this->getAllResults(['type' => 'keyword', 'query' => 'Chuck']);
        if (count($randomJokeData) > 0) {
            return $randomJokeData[array_rand($randomJokeData)]['value'] ?? null;
        }
        return null;
    }
}
