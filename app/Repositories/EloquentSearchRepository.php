<?php

namespace App\Repositories;

use App\Models\Search;
use Illuminate\Support\Facades\Log;
use App\Contracts\SearchRepositoryInterface;
use App\Exceptions\EloquentSearchSaveException;
use Illuminate\Log\Logger;
use Psr\Log\LoggerInterface;

class EloquentSearchRepository implements SearchRepositoryInterface
{
    public function __construct(protected Search $searchModel, protected LoggerInterface $logger)
    {
    }
    public function save(string $type, ?string $query, array $results, ?string $email): Search
    {
        $search = $this->searchModel->newInstance([
            'type' => $type,
            'query' => $query,
            'results' => json_encode($results),
            'email' => $email,
        ]);

        $search->save();

        if (!$search->exists) {
            throw new EloquentSearchSaveException('Error saving search results', 500);
        }

        $this->logger->info("Search results saved. $type | $query");
        return $search;
    }
}
