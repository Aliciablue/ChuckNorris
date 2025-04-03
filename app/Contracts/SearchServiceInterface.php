<?php

namespace App\Contracts;

interface SearchServiceInterface
{
    public function searchFacts(array $data);
    public function getCategories();
    public function getAllResults($validatedData, $type, $query, $email);
    
}