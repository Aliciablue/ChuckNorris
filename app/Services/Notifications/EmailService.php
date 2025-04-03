<?php

namespace App\Services\Notifications;

use Illuminate\Support\Collection;
use App\Jobs\SendSearchResultsEmail;
use App\Contracts\NotificationServiceInterface;

class EmailService implements NotificationServiceInterface
{
    public function sendSearchResultsNotification(Collection $results, string $type, ?string $query, string $email, string $allResultsUrl, string $locale)
    {
        SendSearchResultsEmail::dispatch(
            $results->take(20)->toArray(),
            $type,
            $query ?? '',
            $email,
            $allResultsUrl,
            $locale
        );
         
    }
}
