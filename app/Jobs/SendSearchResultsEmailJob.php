<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\LocaleService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\SearchResultsMail; // Assuming you'll create this Mailable

class SendSearchResultsEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param array $results
     * @param string $searchType
     * @param string $searchTerm
     * @param string $email
     * @param string $allResultsUrl
     * @param string $locale
     * @param int $total
     */
    public function __construct(protected array $results, protected string $searchType, protected string $searchTerm, protected string $email, protected string $allResultsUrl, protected string $locale, protected int $total) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer, LocaleService $localeService): void
    {
        $localeService->setLocale($this->locale);
        $mailer->to($this->email)->send(new SearchResultsMail(
            $this->results,
            $this->searchType,
            $this->searchTerm,
            $this->allResultsUrl,
            $this->total
        ));
    }
    public function getResults(): array
    {
        return $this->results;
    }

    public function getSearchType(): string
    {
        return $this->searchType;
    }

    public function getSearchTerm(): string
    {
        return $this->searchTerm;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAllResultsUrl(): string
    {
        return $this->allResultsUrl;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}
