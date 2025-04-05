<?php

namespace App\Contracts;

interface SearchServiceInterface
{
    public function searchFacts(array $data);
    public function getCategories();
    public function getAllResults($validatedData);
    public function getRandomChuckNorrisJoke();
    public function handleSearchRecord(string $type, string $query, array $allResults, string $email);
    
}