<?php
namespace App\Contracts;

use App\Models\Search;

interface SearchRepositoryInterface
{
    public function save(string $type, ?string $query, array $results, ?string $email): Search;
}