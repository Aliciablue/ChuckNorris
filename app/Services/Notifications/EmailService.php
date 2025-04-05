<?php

namespace App\Services\Notifications;

use App\Http\Requests\SearchRequest;
use App\Jobs\SendSearchResultsEmailJob;
use App\Contracts\NotificationServiceInterface;

class EmailService implements NotificationServiceInterface
{
    public function sendSearchResultsNotification(array $results, string $type, ?string $query, string $email, string $allResultsUrl, string $locale, int $total)
    {
        SendSearchResultsEmailJob::dispatch(
            $results,
            $type,
            $query ?? '',
            $email,
            $allResultsUrl,
            $locale,
            $total
        );
    }
    public function handleSearchResultNotification(SearchRequest $request, array $results, string $type, ?string $query, ?string $email, int $total): void
    {
        if ($request->has('email') && filter_var($request->input('email'), FILTER_VALIDATE_EMAIL)) {
            $allResultsUrl = route('search.results', ['type' => $type, 'query' => $query, 'locale' => app()->getLocale()]);
            $this->sendSearchResultsNotification($results, $type, $query, $email, $allResultsUrl, app()->getLocale(), $total);
            session()->flash('success', __('messages.email_sent_confirmation'));
        }
    }
    
}
