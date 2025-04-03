<?php

namespace App\Repositories;

use App\Models\Search;
use Illuminate\Support\Facades\Log;
use App\Contracts\SearchRepositoryInterface;
use App\Exceptions\EloquentSearchSaveException;

class EloquentSearchRepository implements SearchRepositoryInterface
{
    public function save(string $type, ?string $query, array $results, ?string $email): Search
    {
        $search = Search::create([
            'type' => $type,
            'query' => $query,
            'results' => json_encode($results),
            'email' => $email, // Assuming you want to save the email here too
        ]);
        if (!$search->wasRecentlyCreated) {
            throw new EloquentSearchSaveException('Error saving search results', 500);
        }
        Log::info("Search results saved. $type | $query");
        return $search;
    }
}
