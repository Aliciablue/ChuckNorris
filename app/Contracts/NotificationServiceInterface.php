<?php

namespace App\Contracts;

use App\Http\Requests\SearchRequest;

interface NotificationServiceInterface
{

    public function sendSearchResultsNotification(Array $results, string $type, ?string $query, string $email, string $allResultsUrl, string $locale, int $total);
    public function handleSearchResultNotification(SearchRequest $request, array $results, string $type, ?string $query, ?string $email, int $total): void;

}