<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface NotificationServiceInterface
{

    public function sendSearchResultsNotification(Collection $results, string $type, ?string $query, string $email, string $allResultsUrl, string $locale);
    
}