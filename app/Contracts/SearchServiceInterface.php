<?php

namespace App\Contracts;

interface SearchServiceInterface
{
    public function getCategories();
    public function getAllResults($validatedData);
    public function getRandomChuckNorrisJoke();
}
